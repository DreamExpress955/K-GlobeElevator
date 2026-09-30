/**
 * @file pcanFunctions_multithreaded.cpp
 * @brief Implements multithreaded CAN communication for the K-Globe Elevator Service Controller.
 *
 * Provides the main CAN communication system used by the Service Controller.
 * CAN messages are received in one thread, placed into a priority queue,
 * and processed in a separate thread.
 *
 * The implementation also communicates with the elevator database to
 * process website requests, update floor and door information, control
 * operating modes, log CAN traffic, and coordinate elevator audio
 * announcements.
 */

#include "../include/pcanFunctions_multithreaded.h"
#include "../include/databaseFunctions.h"
#include "../include/audio.h"

#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <unistd.h>
#include <fcntl.h>
#include <libpcan.h>
#include <cstring>

#include <atomic>
#include <condition_variable>
#include <cstdint>
#include <mutex>
#include <queue>
#include <thread>
#include <vector>
#include <csignal>
#include <chrono>

// Existing globals used by the rest of the project.
HANDLE h;
HANDLE h2;
TPCANMsg Txmsg;
DWORD status;
//floor confirmation flag
std::atomic<bool> elev(false);

//CAN handle and synchronization primitives for multithreaded operation.
static HANDLE sharedCANHandle = NULL;
static std::mutex canWriteMutex;
static std::mutex canHandleMutex;
static std::condition_variable canReadyCondition;
static bool canDeviceReady = false;

//Flags for open and closed evelvator doors
static std::atomic<bool> doorOpenReceived(false);

//website flag 0 = normal operation, 1 = stop operation 2 = sabbath mode
static std::atomic<int> websiteFlag(0);

//flag to pause the system when the website flag is 1 or 2
static std::atomic<bool> systemPaused(false);


//signal handler to stop the program gracefully
static volatile std::sig_atomic_t stopRequested =0;

/**
 * @brief Handles operating-system shutdown signals.
 *
 * Sets the shared stopRequested flag when SIGINT or SIGTERM is received.
 * This allows the CAN threads to terminate gracefully.
 *
 * @param signalNumber Signal received by the application.
 *
 * @return void
 */

static void signalHandler(int signalNumber)
{
    if (signalNumber == SIGINT || signalNumber == SIGTERM)
    {
        stopRequested = 1;
    }
}

/**
* @struct QueuedCANMessage
* @brief Stores a CAN message and its queue sequence number.
*
* Combines a TPCANMsg with a sequence number so CAN messages with
* identical IDs can retain their arrival order in the priority queue.
*/

struct QueuedCANMessage
{
    TPCANMsg message;
    std::uint64_t sequenceNumber;
};

/**
* @struct CANMessageCompare
* @brief Defines ordering for CAN messages in the priority queue.
*
* Messages are primarily ordered using their CAN message ID. When two
* messages have the same ID, their sequence numbers are used to maintain
* their arrival order.
*/

struct CANMessageCompare
{
    bool operator()(const QueuedCANMessage& left,
                    const QueuedCANMessage& right) const
    {
        if (left.message.ID != right.message.ID)
        {
            return left.message.ID > right.message.ID;
        }

        return left.sequenceNumber > right.sequenceNumber;
    }
};

static std::priority_queue<
    QueuedCANMessage,
    std::vector<QueuedCANMessage>,
    CANMessageCompare
> canPriorityQueue;

static std::mutex queueMutex;
static std::condition_variable queueCondition;
static std::atomic<bool> receiverRunning(false);
static std::atomic<std::uint64_t> nextSequenceNumber(0);

/**
* @brief Checks whether a CAN message is an ignored status message.
*
* Checks for the status message identified by CAN ID 0x01
* with a message length of 0x04.
*
* @param msg CAN message to examine.
*
* @return true if the message matches the ignored status format.
* @return false otherwise.
*/

static bool isIgnoredStatusMessage(const TPCANMsg& msg)
{
    return msg.ID == 0x01 && msg.LEN == 0x04;
}

/**
* @brief Converts CAN message data into an elevator floor number.
*
* Converts the GO_TO_FLOOR1, GO_TO_FLOOR2, and GO_TO_FLOOR3
* CAN command values into Floors 1, 2, and 3.
*
* @param data CAN data byte containing the floor command.
* @param floorNumber Reference used to store the converted floor number.
*
* @return true if the data contains a valid floor command.
* @return false if the CAN command is not recognized.
*/

static bool getFloorFromMessageData(BYTE data, int& floorNumber)
{
    switch (data)
    {
        case GO_TO_FLOOR1:
            floorNumber = 1;
            return true;

        case GO_TO_FLOOR2:
            floorNumber = 2;
            return true;

        case GO_TO_FLOOR3:
            floorNumber = 3;
            return true;

        default:
            return false;
    }
}

/**
* @brief Converts an elevator floor number into CAN message data.
*
* Converts Floors 1, 2, and 3 into their corresponding
* GO_TO_FLOOR CAN command values.
*
* @param floorNumber Elevator floor number to convert.
*
* @return GO_TO_FLOOR1 for Floor 1.
* @return GO_TO_FLOOR2 for Floor 2.
* @return GO_TO_FLOOR3 for Floor 3.
* @return -1 if the floor number is invalid.
*/

static int getFloorMessageData(int floorNumber)
{
    switch (floorNumber)
    {
        case 1:
            return GO_TO_FLOOR1;

        case 2:
            return GO_TO_FLOOR2;

        case 3:
            return GO_TO_FLOOR3;

        default:
            return -1;
    }
}

/**
 * @brief Removes all pending messages from the CAN priority queue.
 *
 * Locks the shared CAN queue and removes every queued message.
 * This is used when CAN request processing must be stopped or reset.
 *
 * @return void
 */

static void clearCANQueue()
{
    std::lock_guard<std::mutex> queueLock(queueMutex);

    while (!canPriorityQueue.empty())
    {
        canPriorityQueue.pop();
    }
}

/**
 * @brief Adds an elevator floor request to the CAN processing queue.
 *
 * Converts the supplied floor number into its CAN command value, creates
 * a standard CAN message, assigns a sequence number, and adds the message
 * to the shared priority queue.
 *
 * @param floorNumber Elevator floor being requested.
 * @param ID CAN message ID associated with the request.
 *
 * @return void
 */

static void addFloorRequestToQueue(int floorNumber, int ID)
{
    int floorData = getFloorMessageData(floorNumber);

    if (floorData < 0)
    {
        printf("Invalid floor request: %d\n", floorNumber);
        return;
    }

    TPCANMsg floorRequest;
    memset(&floorRequest, 0, sizeof(floorRequest));

    floorRequest.ID = ID;
    floorRequest.MSGTYPE = MSGTYPE_STANDARD;
    floorRequest.LEN = 1;
    floorRequest.DATA[0] = static_cast<BYTE>(floorData);

    QueuedCANMessage queuedMessage;
    queuedMessage.message = floorRequest;
    queuedMessage.sequenceNumber = nextSequenceNumber++;

    {
        std::lock_guard<std::mutex> lock(queueMutex);
        canPriorityQueue.push(queuedMessage);
    }

    printf("Floor %d request added to CAN queue\n", floorNumber);

    queueCondition.notify_one();
}

/**
 * @brief Transmits a CAN message through the shared PCAN interface.
 *
 * Waits until the CAN device has been initialized by the receiver thread,
 * creates a standard one-byte CAN message, and transmits the message
 * through the shared CAN handle.
 *
 * Successful CAN transmissions are also recorded in the Elevator
 * database using db_logCANMessage().
 *
 * @param id CAN message identifier.
 * @param data Data byte to transmit.
 * @param description Description stored with the CAN database log.
 *
 * @return 0 when the CAN message is transmitted successfully.
 * @return -1 when the CAN interface is unavailable or shutting down.
 * @return PCAN error status when CAN_Write() fails.
 */

int pcanTx(int id, int data, std::string description)
{
   HANDLE transmitHandle = NULL;

    {
        std::unique_lock<std::mutex> handleLock(canHandleMutex);

        printf("Waiting for CAN device initialization...\n");

        canReadyCondition.wait(
            handleLock,
            []()
            {
                return canDeviceReady ||
                !receiverRunning ||
                stopRequested;
            }
        );
        //check if everything is set correctly to transmit the message
        if (!canDeviceReady ||
            !receiverRunning ||
            stopRequested ||
            sharedCANHandle == NULL)
        {
            printf("CAN device is unavailable or shutting down\n");
            return -1;
        }

        transmitHandle = sharedCANHandle;
    }
    if(transmitHandle == NULL){
        printf("CAN device is not ready for transmission\n");
        return -1;
    }

    std::lock_guard<std::mutex> writeLock(canWriteMutex);

    TPCANMsg txMessage;
    memset(&txMessage, 0, sizeof(txMessage));

    txMessage.ID = static_cast<DWORD>(id);
    txMessage.MSGTYPE = MSGTYPE_STANDARD;
    txMessage.LEN = 1;
    txMessage.DATA[0] = static_cast<BYTE>(data);

    DWORD writeStatus = CAN_Write(transmitHandle, &txMessage);

    if (writeStatus != PCAN_NO_ERROR)
    {
        printf(
            "CAN_Write error: 0x%x ID:0x%04x DATA:0x%02x\n",
            static_cast<unsigned int>(writeStatus),
            static_cast<unsigned int>(txMessage.ID),
            static_cast<unsigned int>(txMessage.DATA[0])
        );
        
        return static_cast<int>(writeStatus);
    }

    printf(
        "CAN transmitted ID:0x%04x DATA:0x%02x\n",
        static_cast<unsigned int>(txMessage.ID),
        static_cast<unsigned int>(txMessage.DATA[0])
    );

    db_logCANMessage(0,txMessage.ID,txMessage.LEN,txMessage.DATA,description.c_str());
    return 0;
}

/**
 * @brief Receives a CAN message directly from the PCAN interface.
 *
 * Opens the PCAN USB device, initializes communication at 125 kbit/s,
 * and waits for a valid CAN message.
 *
 * Received CAN messages are logged to the Elevator database. Ignored
 * status messages are skipped until a message requiring processing
 * is received.
 *
 * @return The received TPCANMsg.
 *
 * @note A zero-initialized TPCANMsg is returned if the PCAN device
 * cannot be opened or initialized.
 */

TPCANMsg pcanRxWithDetails()
{
    TPCANMsg receivedMessage;
    memset(&receivedMessage, 0, sizeof(receivedMessage));

    h2 = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    if (h2 == NULL)
    {
        printf("Unable to open PCAN receive channel\n");
        return receivedMessage;
    }

    status = CAN_Init(h2, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    if (status != PCAN_NO_ERROR)
    {
        printf("CAN_Init receive error: 0x%x\n",
               static_cast<unsigned int>(status));
        CAN_Close(h2);
        return receivedMessage;
    }

    status = CAN_Status(h2);

    while (true)
    {
        status = CAN_Read(h2, &receivedMessage);

        if (status == PCAN_RECEIVE_QUEUE_EMPTY)
        {
            usleep(10000);
            continue;
        }

        if (status != PCAN_NO_ERROR)
        {
            printf("CAN_Read error: 0x%x\n",
                   static_cast<unsigned int>(status));
            continue;
        }

        if (status == PCAN_NO_ERROR)
        {
            db_logCANMessage(
                0, // nodeID
                receivedMessage.ID,
                receivedMessage.LEN,
                receivedMessage.DATA,
                "Received CAN message"
            );
        }
        

        if (!isIgnoredStatusMessage(receivedMessage))
        {
            break;
        }
        db_logCANMessage(
            0, // nodeID
            receivedMessage.ID,
            receivedMessage.LEN,
            receivedMessage.DATA,
            "Received CAN message"
        );
    }

    CAN_Close(h2);
    return receivedMessage;
}

/**
 * @brief Receives CAN messages and monitors website elevator commands.
 *
 * Runs as the CAN receiver thread for the Service Controller. The
 * function opens and initializes the PCAN device, publishes the shared
 * CAN handle, receives physical CAN messages, and places valid messages
 * into the priority queue.
 *
 * The receiver also polls the Elevator database approximately every
 * 250 milliseconds for:
 * - Operating mode changes.
 * - Website floor requests.
 * - Floor-controller requests.
 *
 * Door-open and door-closed CAN messages are processed immediately and
 * their status is written to the Elevator database.
 *
 * Maintenance and Sabbath modes can pause normal physical CAN request
 * processing and clear queued requests.
 *
 * @return void
 */

static void canReceiverThread()
{
    const char* devicePath = "/dev/pcanusb32";
    HANDLE receiveHandle = NULL;

    std::chrono::steady_clock::time_point lastDatabaseCheck =
        std::chrono::steady_clock::now();

    const std::chrono::milliseconds databaseCheckInterval(250);

    printf("Waiting for %s to become available...\n", devicePath);

    while (receiverRunning && receiveHandle == NULL)
    {
        receiveHandle = LINUX_CAN_Open(devicePath, O_RDWR);

        if (receiveHandle == NULL)
        {
            printf(
                "%s is unavailable. Retrying in 1 second...\n",
                devicePath
            );

            sleep(1);
        }
    }

    if (!receiverRunning || stopRequested)
    {
        printf("CAN startup cancelled\n");
        queueCondition.notify_all();
        return;
    }

    printf("%s opened successfully\n", devicePath);

    DWORD receiveStatus =
        CAN_Init(receiveHandle, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    if (receiveStatus != PCAN_NO_ERROR)
    {
        printf("Receiver thread CAN_Init error: 0x%x\n",
               static_cast<unsigned int>(receiveStatus));

        CAN_Close(receiveHandle);
        receiverRunning = false;
        queueCondition.notify_all();
        return;
    }
    else {
    std::lock_guard<std::mutex> lock(canHandleMutex);

    sharedCANHandle = receiveHandle;
    canDeviceReady = true;
    }

    canReadyCondition.notify_all();
    
    

    printf("CAN receiver thread started\n");

    while (receiverRunning && !stopRequested)
    {
        std::chrono::steady_clock::time_point currentTime = std::chrono::steady_clock::now();

        // read the ebsite commands
        if(currentTime - lastDatabaseCheck >= databaseCheckInterval)
        {
            int currentWebsiteFlag = db_getStopFlag();
            websiteFlag.store(currentWebsiteFlag);

            lastDatabaseCheck = currentTime;


            //maintanence mode (stop all physical CAN readings)
            if (websiteFlag.load() == 2){
                if (!systemPaused.load())
                {
                    systemPaused.store(true);
                    clearCANQueue();

                    printf(
                        "Stop flag is high. "
                        "CAN reading and queue processing stopped\n"
                    );

                    queueCondition.notify_all();
                }
            }
            //sabbath mode (stop all physical CAN readings and ignore all requests)
            else if (websiteFlag.load() == 1){
                if (!systemPaused.load())
                {
                    systemPaused.store(true);
                    clearCANQueue();

                    printf(
                        "Sabbath mode is active. "
                        "CAN reading and queue processing stopped\n"
                    );
                    int floor = 0;
                    queueCondition.notify_all();
                    while(websiteFlag.load() == 1 && !stopRequested){

                        //add floor request to the queue (1 then 2, then 3 then back to 1)
                        addFloorRequestToQueue((floor % 3) + 1, ID_CC_TO_SC);
                        sleep(3);
                        printf("test\n");
                        //check database if the flag is changed 
                        currentWebsiteFlag = db_getStopFlag();
                        websiteFlag.store(currentWebsiteFlag);
                        playFloor((floor % 3) + 1);
                        sleep(2);
                        floor++;
                    }
                }
            }
            //normal mode
            else {
                systemPaused.store(false);
                queueCondition.notify_all();
            }

            //check if there is a flor request from the website
            int requestedFloor = db_getRequestedFloor();
            int requestType = db_getRequestType();
            if (requestedFloor >= 1 &&
                requestedFloor <= 3 &&
                (requestType == 1 || requestType == 0))
            {
                if (requestType == 0)
                {
                    addFloorRequestToQueue(requestedFloor, (ID_WEBSITE + requestedFloor));
                }
                else
                {

                    addFloorRequestToQueue(requestedFloor, ID_WEBSITE);
                }

                //clear the request so it doesn't add it again
                db_clearWebsiteRequest();
            }

        }

        TPCANMsg receivedMessage;
        memset(&receivedMessage, 0, sizeof(receivedMessage));

        receiveStatus = CAN_Read(receiveHandle, &receivedMessage);

        if (receiveStatus == PCAN_RECEIVE_QUEUE_EMPTY)
        {
            usleep(10000);
            continue;
        }

        if (receiveStatus != PCAN_NO_ERROR)
        {
            printf("Receiver thread CAN_Read error: 0x%x\n",
                   static_cast<unsigned int>(receiveStatus));

            usleep(100000);
            continue;
        }

        if (isIgnoredStatusMessage(receivedMessage))
        {
            continue;
        }
        if (receivedMessage.DATA[0] == 0x08)
        {
            doorOpenReceived = false;

            printf("Door CLOSE\n");
            db_updateDoor(0);
            continue;
        }

        else if (receivedMessage.DATA[0]== 0x09)
        {
            doorOpenReceived = true;
            db_updateDoor(1);
            printf("Door OPEN\n");


            continue;
        }
        else{
            if (!systemPaused.load()){
                QueuedCANMessage queuedMessage;
                queuedMessage.message = receivedMessage;
                queuedMessage.sequenceNumber = nextSequenceNumber++;

                //this if statement is to prevent the terminal from being cluttered with the elevator confirmations.
                if(!(receivedMessage.ID == ID_EC_TO_ALL && elev == false)){
                    printf("Queued CAN message ID 0x%04x\n DATA: 0x%02x\n",static_cast<unsigned int>(receivedMessage.ID),static_cast<unsigned int>(receivedMessage.DATA[0]));
                }

                
                std::lock_guard<std::mutex> lock(queueMutex);
                canPriorityQueue.push(queuedMessage);
                
                queueCondition.notify_one();
            }
        }
        
            
        
        

        queueCondition.notify_one();
        }
    
    {
    std::lock_guard<std::mutex> writeLock(canWriteMutex);
    std::lock_guard<std::mutex> handleLock(canHandleMutex);

    canDeviceReady = false;
    sharedCANHandle = NULL;

    CAN_Close(receiveHandle);
    }
    queueCondition.notify_all();
    canReadyCondition.notify_all();
    printf("CAN receiver thread stopped\n");
    
}

/**
 * @brief Processes CAN messages stored in the priority queue.
 *
 * Runs independently from the CAN receiver thread. The processor waits
 * for queued CAN messages and determines the appropriate action based
 * on each message's CAN ID.
 *
 * Supported message sources include:
 * - Supervisory Controller.
 * - Elevator Controller.
 * - Car Controller.
 * - Floor 1 Controller.
 * - Floor 2 Controller.
 * - Floor 3 Controller.
 * - Website car controller.
 * - Website floor controllers.
 *
 * Depending on the received message, this function can transmit a new
 * CAN command, update the Elevator database, log CAN activity, update
 * floor information, or play a floor audio announcement.
 *
 * @return void
 */

static void canProcessorThread()
{
    int floorNumber = 1;
    bool requestWasTransmitted = false;
    printf("CAN processing thread started\n");

    while (true)
    {
        while(doorOpenReceived && !stopRequested){
            //infinte loop until door is closed or the thread is stopped
        }
        TPCANMsg msg;
        memset(&msg, 0, sizeof(msg));

        {
            std::unique_lock<std::mutex> lock(queueMutex);

            queueCondition.wait(
                lock,
                []()
                {
                    return !canPriorityQueue.empty() ||
                           !receiverRunning;
                });

            if (!receiverRunning && canPriorityQueue.empty())
            {
                break;
            }

            msg = canPriorityQueue.top().message;
            canPriorityQueue.pop();
        }

        switch (msg.ID)
        {
            case ID_SC_TO_EC:
            {
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    printf("Supervisory Controller requested floor %d\n",
                           floorNumber);
                }
                else
                {
                    printf("Supervisory Controller sent unknown data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                break;
            }

            case ID_EC_TO_ALL:
            {
                if (elev == false && (msg.DATA[0] == 0x05 || msg.DATA[0] == 0x06 || msg.DATA[0] ==0x07)){
                    if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                    {
                        printf("Elevator Controller announces elevator is at floor %d\n",
                            floorNumber);
                        playFloor(floorNumber);
                        db_setFloorNum(floorNumber);

                        std::string info = "Elevator Controller announces elevator is at floor:" + std::to_string(floorNumber);
                        db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());
                    }
                    else
                    {
                        printf("Elevator Controller sent unknown floor data: 0x%02x\n",
                            static_cast<unsigned int>(msg.DATA[0]));
                    }
                
                elev = true;
            }
                break;
            }

            case ID_CC_TO_SC:
            {
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    doorOpenReceived = false;


                    printf("Car Controller requested floor %d\n",
                           floorNumber);
                    //upload to data base
                    std::string info = "Car Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, msg.DATA[0], "From Car Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit car Controller request\n"
                        );
                    }
                    elev = false;
                }
                else
                {
                    printf("Car Controller sent unknown floor data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                
                break;
            }

            case ID_F1_TO_SC:
            {
                if (msg.DATA[0] == 0x01)
                {

                    floorNumber = 1;
                    printf("Floor 1 Controller made a request\n");
                    //upload to data base
                    std::string info = "Floor 1 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());
                        
                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1, "From Floor 1 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Floor 1 Controller request\n"
                        );
                    }
                }
                else
                {
                    printf("Floor 1 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }

            case ID_F2_TO_SC:
            {
                if (msg.DATA[0] == 0x01)
                {


                    floorNumber = 2;
                    printf("Floor 2 Controller made a request\n");

                    //upload to data base
                    std::string info = "Floor 2 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2, "From Floor 2 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Floor 2 request\n"
                        );
                    }
                }
                else
                {
                    printf("Floor 2 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }

            case ID_F3_TO_SC:
            {
                if (msg.DATA[0] == 0x01)
                {

                    floorNumber = 3;
                    printf("Floor 3 Controller made a request\n");

                    //upload to data base
                    std::string info = "Floor 3 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3, "From Floor 3 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Floor 3 Controller request\n"
                        );
                    }
                }
                else
                {
                    printf("Floor 3 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }
            case ID_WEBSITE:
            {
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    doorOpenReceived = false;


                    printf("Website Car Controller requested floor %d\n",
                           floorNumber);
                    //upload to data base
                    std::string info = "Website Car Controller Requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, msg.DATA[0], "From WebSite");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Website car Controller request\n"
                        );
                    }
                    elev = false;
                }
                else
                {
                    printf("Website Car Controller sent unknown floor data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                
                break;
            }
            case (ID_WEBSITE + 1):
            {
                if (msg.DATA[0] == 0x05)
                {

                    floorNumber = 1;
                    printf("Floor 1 Controller made a request\n");
                    //upload to data base
                    std::string info = "Floor 1 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());
                        
                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1, "From Floor 1 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Website Floor 1 Controller request\n"
                        );
                    }
                }
                else
                {
                    printf("Website Floor 1 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }

            case (ID_WEBSITE + 2):
            {
                if (msg.DATA[0] == 0x06)
                {


                    floorNumber = 2;
                    printf("Website Floor 2 Controller made a request\n");

                    //upload to data base
                    std::string info = "Website Floor 2 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2, "From Website Floor 2 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Website Floor 2 request\n"
                        );
                    }
                }
                else
                {
                    printf("Website Floor 2 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }

            case (ID_WEBSITE + 3):
            {
                if (msg.DATA[0] == 0x07)
                {

                    floorNumber = 3;
                    printf("Website Floor 3 Controller made a request\n");

                    //upload to data base
                    std::string info = "Floor 3 Controller requested floor:" + std::to_string(floorNumber);
                    db_logCANMessage(0,msg.ID,msg.LEN,msg.DATA,info.c_str());

                    int transmitStatus =
                        pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3, "From Website Floor 3 Controller");

                    if (transmitStatus == 0)
                    {
                        db_setFloorNum(floorNumber);
                        requestWasTransmitted = true;
                    }
                    else
                    {
                        printf(
                            "Failed to transmit Website Floor 3 Controller request\n"
                        );
                    }
                }
                else
                {
                    printf("Website Floor 3 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                elev = false;
                break;
            }
            default:
            {
                printf("Unknown CAN message ID: 0x%04x\n",
                       static_cast<unsigned int>(msg.ID));
                break;
            }
            
        }
        if (requestWasTransmitted)
        {
            requestWasTransmitted = false;
            sleep(4); //this sleep is need so that the service controller waits for the door open message.
        }

    }

    printf("CAN processing thread stopped\n");
}

/**
 * @brief Starts the multithreaded CAN communication system.
 *
 * Initializes the shared CAN state and installs SIGINT and SIGTERM
 * handlers before starting the CAN receiver and processor threads.
 *
 * The function remains active until a stop is requested. It then
 * signals both threads to stop, waits for them to finish, and resets
 * the shared CAN device state.
 *
 * @return void
 */

void pcanRxWithDetailsMultithreaded()
{
    if (receiverRunning)
    {
        printf("Multithreaded CAN mode is already running\n");
        return;
    }
    stopRequested = 0;
    std::signal(SIGINT, signalHandler);
    std::signal(SIGTERM, signalHandler);

    // Clear old queued messages.
    {
        std::lock_guard<std::mutex> queueLock(queueMutex);

        while (!canPriorityQueue.empty())
        {
            canPriorityQueue.pop();
        }
    }

    // Reset the shared CAN state.
    // The braces are essential so the mutex is released
    // before the receiver thread starts.
    {
        std::lock_guard<std::mutex> handleLock(canHandleMutex);

        sharedCANHandle = NULL;
        canDeviceReady = false;
    }

    nextSequenceNumber = 0;
    receiverRunning = true;

    printf("\nStarting multithreaded CAN mode\n");
    printf("Press Ctrl+C to terminate the program\n");

    std::thread receiverThread(canReceiverThread);
    std::thread processorThread(canProcessorThread);
    while (receiverRunning && !stopRequested)
    {
        usleep(10000);
    }
    printf("\nStopping CAN threads...\n");


    receiverRunning = false;
    queueCondition.notify_all();
    canReadyCondition.notify_all();

    if(receiverThread.joinable()){
        receiverThread.join();
    }
    if (processorThread.joinable()){
        processorThread.join();
    }

    {
        std::lock_guard<std::mutex> handleLock(canHandleMutex);

        sharedCANHandle = NULL;
        canDeviceReady = false;
    }

    printf("Multithreaded CAN mode stopped\n");
    

}

/**
 * @brief Requests shutdown of the multithreaded CAN system.
 *
 * Sets the stop and receiver flags, then notifies the CAN queue and
 * device condition variables so waiting threads can wake up and
 * terminate.
 *
 * @return void
 */

void stopPcanMultithreaded()
{
    stopRequested = 1;
    receiverRunning = false;

    queueCondition.notify_all();
    canReadyCondition.notify_all();
}