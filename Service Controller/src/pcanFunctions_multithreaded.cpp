#include "../include/pcanFunctions_multithreaded.h"
#include "../include/databaseFunctions.h"

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

// Existing globals used by the rest of the project.
HANDLE h;
HANDLE h2;
TPCANMsg Txmsg;
DWORD status;
int elev = 0;
int elev2 = 0;

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

static void printCANMessage(const TPCANMsg& msg)
{
    printf("  - ID:%04x LEN:%01x DATA:",
           static_cast<unsigned int>(msg.ID),
           static_cast<unsigned int>(msg.LEN));

    for (int i = 0; i < msg.LEN; ++i)
    {
        printf("%02x ", static_cast<unsigned int>(msg.DATA[i]));
    }

    printf("\n");
}

int pcanTx(int id, int data)
{
    h = LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    if (h == NULL)
    {
        printf("Unable to open PCAN transmit channel\n");
        return -1;
    }

    status = CAN_Init(h, CAN_BAUD_125K, CAN_INIT_TYPE_ST);

    if (status != PCAN_NO_ERROR)
    {
        printf("CAN_Init transmit error: 0x%x\n",
               static_cast<unsigned int>(status));
        CAN_Close(h);
        return static_cast<int>(status);
    }

    status = CAN_Status(h);

    Txmsg.ID = id;
    Txmsg.MSGTYPE = MSGTYPE_STANDARD;
    Txmsg.LEN = 1;
    Txmsg.DATA[0] = data;

    sleep(1);
    status = CAN_Write(h, &Txmsg);

    if (status != PCAN_NO_ERROR)
    {
        printf("CAN_Write error: 0x%x\n",
               static_cast<unsigned int>(status));
    }

    CAN_Close(h);
    return static_cast<int>(status);
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

        if (!isIgnoredStatusMessage(receivedMessage))
        {
            break;
        }
    }

    CAN_Close(h2);
    return receivedMessage;
}

static void canReceiverThread()
{
    HANDLE receiveHandle =
        LINUX_CAN_Open("/dev/pcanusb32", O_RDWR);

    if (receiveHandle == NULL)
    {
        printf("Receiver thread could not open /dev/pcanusb32\n");
        receiverRunning = false;
        queueCondition.notify_all();
        return;
    }

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

    receiveStatus = CAN_Status(receiveHandle);

    printf("CAN receiver thread started\n");

    while (receiverRunning)
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

        QueuedCANMessage queuedMessage;
        queuedMessage.message = receivedMessage;
        queuedMessage.sequenceNumber = nextSequenceNumber++;

        {
            std::lock_guard<std::mutex> lock(queueMutex);
            canPriorityQueue.push(queuedMessage);
        }

        printf("Queued CAN message ID 0x%04x\n",
               static_cast<unsigned int>(receivedMessage.ID));

        queueCondition.notify_one();
    }

    CAN_Close(receiveHandle);
    printf("CAN receiver thread stopped\n");
    queueCondition.notify_all();
}

static void canProcessorThread()
{
    int floorNumber = 1;

    printf("CAN processing thread started\n");

    while (receiverRunning || !canPriorityQueue.empty())
    {
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

        printf("\nProcessing CAN message\n");
        printCANMessage(msg);

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
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    printf("Elevator Controller announces elevator is at floor %d\n",
                           floorNumber);

                    db_setFloorNum(floorNumber);

                    printf("Door Open\n");
                    sleep(2);
                    printf("Door Close\n");
                }
                else
                {
                    printf("Elevator Controller sent unknown floor data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                break;
            }

            case ID_CC_TO_SC:
            {
                if (getFloorFromMessageData(msg.DATA[0], floorNumber))
                {
                    printf("Car Controller requested floor %d\n",
                           floorNumber);

                    pcanTx(ID_SC_TO_EC, msg.DATA[0]);
                    db_setFloorNum(floorNumber);
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
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR1);
                    db_setFloorNum(floorNumber);
                }
                else
                {
                    printf("Floor 1 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                break;
            }

            case ID_F2_TO_SC:
            {
                if (msg.DATA[0] == 0x01)
                {
                    floorNumber = 2;
                    printf("Floor 2 Controller made a request\n");
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR2);
                    db_setFloorNum(floorNumber);
                }
                else
                {
                    printf("Floor 2 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                break;
            }

            case ID_F3_TO_SC:
            {
                if (msg.DATA[0] == 0x01)
                {
                    floorNumber = 3;
                    printf("Floor 3 Controller made a request\n");
                    pcanTx(ID_SC_TO_EC, GO_TO_FLOOR3);
                    db_setFloorNum(floorNumber);
                }
                else
                {
                    printf("Floor 3 Controller sent unexpected data: 0x%02x\n",
                           static_cast<unsigned int>(msg.DATA[0]));
                }
                break;
            }

            default:
            {
                printf("Unknown CAN message ID: 0x%04x\n",
                       static_cast<unsigned int>(msg.ID));
                break;
            }
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

    {
        std::lock_guard<std::mutex> lock(queueMutex);

        while (!canPriorityQueue.empty())
        {
            canPriorityQueue.pop();
        }
    }

    nextSequenceNumber = 0;
    receiverRunning = true;

    printf("\nStarting multithreaded CAN mode\n");
    printf("Press Ctrl+C to terminate the program\n");

    std::thread receiverThread(canReceiverThread);
    std::thread processorThread(canProcessorThread);

    receiverThread.join();

    receiverRunning = false;
    queueCondition.notify_all();

    processorThread.join();
}

void stopPcanMultithreaded()
{
    receiverRunning = false;
    queueCondition.notify_all();
}