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


//signal handler to stop the program gracefully
static volatile std::sig_atomic_t stopRequested =0;

static void signalHandler(int signalNumber)
{
    if (signalNumber == SIGINT || signalNumber == SIGTERM)
    {
        stopRequested = 1;
    }
}

struct QueuedCANMessage
{
    TPCANMsg message;
    std::uint64_t sequenceNumber;
};

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

static bool isIgnoredStatusMessage(const TPCANMsg& msg)
{
    return msg.ID == 0x01 && msg.LEN == 0x04;
}

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

static void canReceiverThread()
{
    const char* devicePath = "/dev/pcanusb32";
    HANDLE receiveHandle = NULL;

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
        if (receivedMessage.ID == 0x08)
        {
            doorOpenReceived = true;

            printf("Door OPEN\n");

            continue;
        }

        else if (receivedMessage.ID == 0x09)
        {
            doorOpenReceived = false;

            printf("Door CLOSE\n");


            continue;
        }
        else{
        QueuedCANMessage queuedMessage;
        queuedMessage.message = receivedMessage;
        queuedMessage.sequenceNumber = nextSequenceNumber++;

        {
            std::lock_guard<std::mutex> lock(queueMutex);
            canPriorityQueue.push(queuedMessage);
        }
        
        //printf("Queued CAN message ID 0x%04x\n DATA: 0x%02x\n",static_cast<unsigned int>(receivedMessage.ID),static_cast<unsigned int>(receivedMessage.DATA[0]));

        queueCondition.notify_one();
        }
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
                sleep(3);
                break;
            }

            case ID_EC_TO_ALL:
            {
                if (elev == false){
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    printf("Elevator Controller announces elevator is at floor %d\n",
                           floorNumber);
                    playFloor(floorNumber);
                    db_setFloorNum(floorNumber);
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
                            "Failed to transmit car Controller request\n"
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
                            "Failed to transmit car Controller request\n"
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

            default:
            {
                printf("Unknown CAN message ID: 0x%04x\n",
                       static_cast<unsigned int>(msg.ID));
                break;
            }
            
        }
        if(requestWasTransmitted){
            sleep(3);
            requestWasTransmitted = false;
        }
    }

    printf("CAN processing thread stopped\n");
}

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

void stopPcanMultithreaded()
{
    stopRequested = 1;
    receiverRunning = false;

    queueCondition.notify_all();
    canReadyCondition.notify_all();
}