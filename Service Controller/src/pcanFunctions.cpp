/**
 * @file pcanFunctions.cpp
 * @brief Implements basic PCAN communication for the K-Globe Elevator Service Controller.
 *
 * Provides the basic non-multithreaded CAN transmit and receive functions
 * used by the Service Controller.
 *
 * The PCAN USB interface is configured for standard CAN 2.0 communication
 * at 125 kbit/s. This file also contains an older detailed CAN receive
 * implementation that is currently commented out and preserved for reference.
 */

#include "../include/pcanFunctions.h"

#include <stdio.h>
#include <stdlib.h>
#include <stdlib.h>
#include <errno.h>
#include <unistd.h>
#include <signal.h>
#include <string.h>
#include <fcntl.h>
#include <unistd.h>
#include <ctype.h>
#include <libpcan.h>
#include <queue>


/* ============================================================
 * Global Variables
 * ============================================================ */

/** @brief PCAN handle used for CAN transmission. */
HANDLE h;

/** @brief PCAN handle used for CAN reception. */
HANDLE h2;

/** @brief CAN message structure used for transmitted messages. */
TPCANMsg Txmsg;

/** @brief CAN message structure used for received messages. */
TPCANMsg Rxmsg;

/** @brief Stores the status returned by PCAN operations. */
DWORD status;

/**
 * @brief Elevator Controller confirmation flag.
 *
 * Used to prevent repeated Elevator Controller messages from being
 * printed multiple times.
 */
int elev = 0;

/**
 * @brief Additional elevator state flag used by the Service Controller.
 */
int elev2 = 0;

/**
 * @brief Queue used to temporarily store received CAN messages.
 */
std::queue<TPCANMsg> canQueue;


/* ============================================================
 * PCAN Communication Functions
 * ============================================================ */

/**
 * @brief Transmits a CAN message through the PCAN interface.
 *
 * Opens the PCAN USB channel and initializes standard CAN 2.0
 * communication at 125 kbit/s. A one-byte CAN message is created
 * using the supplied CAN ID and data value before being transmitted.
 *
 * The PCAN channel is closed after the transmission is complete.
 *
 * @param id CAN message identifier.
 * @param data Data byte to transmit.
 *
 * @return PCAN status value returned by CAN_Write().
 */
int pcanTx(int id, int data)
{
    // Open the PCAN channel.
    h = LINUX_CAN_Open(
        "/dev/pcanusb32",
        O_RDWR
    );

    // Initialize CAN 2.0 at 125 kbit/s using standard frames.
    status = CAN_Init(
        h,
        CAN_BAUD_125K,
        CAN_INIT_TYPE_ST
    );

    // Clear/check the CAN channel before transmission.
    status = CAN_Status(h);

    // Configure the CAN message.
    Txmsg.ID = id;
    Txmsg.MSGTYPE = MSGTYPE_STANDARD;
    Txmsg.LEN = 1;
    Txmsg.DATA[0] = data;

    sleep(1);

    // Transmit the CAN message.
    status = CAN_Write(
        h,
        &Txmsg
    );

    // Close the CAN channel.
    CAN_Close(h);

    return static_cast<int>(status);
}


/**
 * @brief Receives a specified number of CAN messages.
 *
 * Opens the PCAN USB channel and initializes standard CAN 2.0
 * communication at 125 kbit/s.
 *
 * The function waits until the requested number of valid CAN messages
 * have been received. PCAN status messages are ignored and valid
 * messages are displayed in the terminal.
 *
 * @param num_msgs Number of CAN messages to receive.
 *
 * @return Data byte from the last CAN message received.
 */
int pcanRx(int num_msgs)
{
    int i = 0;

    // Open the PCAN receive channel.
    h2 = LINUX_CAN_Open(
        "/dev/pcanusb32",
        O_RDWR
    );

    // Initialize CAN 2.0 at 125 kbit/s using standard frames.
    status = CAN_Init(
        h2,
        CAN_BAUD_125K,
        CAN_INIT_TYPE_ST
    );

    // Clear/check the CAN channel before receiving messages.
    status = CAN_Status(h2);

    // Clear the terminal before displaying received messages.
    system("@cls||clear");

    printf(
        "\nReady to receive message(s) over CAN bus\n"
    );

    // Read the requested number of CAN messages.
    while (i < num_msgs)
    {
        // Wait while the receive queue is empty.
        while (
            (status = CAN_Read(h2, &Rxmsg))
            == PCAN_RECEIVE_QUEUE_EMPTY
        )
        {
            sleep(1);
        }

        // Display any PCAN error code.
        if (status != PCAN_NO_ERROR)
        {
            printf(
                "Error 0x%x\n",
                static_cast<int>(status)
            );

            // break;
        }

        // Ignore PCAN status messages on the CAN bus.
        if (
            Rxmsg.ID != 0x01 &&
            Rxmsg.LEN != 0x04
        )
        {
            // Display the received CAN message.
            printf(
                "  - R ID:%4x LEN:%1x DATA:%02x \n",
                static_cast<int>(Rxmsg.ID),
                static_cast<int>(Rxmsg.LEN),
                static_cast<int>(Rxmsg.DATA[0])
            );

            i++;
        }
    }

    // Close the CAN receive channel.
    CAN_Close(h2);

    // Return the final data byte received.
    return static_cast<int>(Rxmsg.DATA[0]);
}


/* ============================================================
 * Previous Detailed CAN Receive Implementation
 * ============================================================
 *
 * The function below is currently disabled and has been preserved
 * for reference. It contains an earlier implementation for receiving,
 * queuing, identifying, and displaying CAN messages from the different
 * elevator controllers.
 */

/*

/**
 * @brief Receives and processes a detailed CAN message.
 *
 * Opens the PCAN receive channel, waits for a valid CAN message,
 * places the message into a queue, and identifies the sender using
 * the CAN message ID.
 *
 * CAN messages from the Supervisory Controller, Elevator Controller,
 * Car Controller, and Floor Controllers are interpreted and displayed.
 *
 * @return The CAN message that was processed.
 *
 * @note This function is currently disabled and preserved for reference.
 */
TPCANMsg pcanRxWithDetails()
{
    int i = 0;
    TPCANMsg msg;

    // Open a CAN channel.
    h2 = LINUX_CAN_Open(
        "/dev/pcanusb32",
        O_RDWR
    );

    // Initialize CAN 2.0 at 125 kbit/s using standard frames.
    status = CAN_Init(
        h2,
        CAN_BAUD_125K,
        CAN_INIT_TYPE_ST
    );

    // Clear/check the CAN channel before receiving messages.
    status = CAN_Status(h2);

    // Clear the terminal if required.
    // system("@cls||clear");

    // Wait for one valid CAN message.
    while (i < 1)
    {
        while (
            (status = CAN_Read(h2, &Rxmsg))
            == PCAN_RECEIVE_QUEUE_EMPTY
        )
        {
            sleep(1);
        }

        if (status != PCAN_NO_ERROR)
        {
            printf(
                "Error 0x%x\n",
                static_cast<int>(status)
            );

            // break;
        }

        // Ignore PCAN status messages.
        if (
            Rxmsg.ID != 0x01 &&
            Rxmsg.LEN != 0x04
        )
        {
            // Add the received CAN message to the queue.
            canQueue.push(Rxmsg);

            // Get the next CAN message from the queue.
            msg = canQueue.front();

            switch (msg.ID)
            {
                case 0x0100:
                {
                    printf(
                        "Supervisoury Controller requested floor "
                    );

                    elev = 0;

                    switch (msg.DATA[0])
                    {
                        case 0x5:
                            printf("1");
                            break;

                        case 0x6:
                            printf("2");
                            break;

                        case 0x7:
                            printf("3");
                            break;
                    }

                    break;
                }


                case 0x0101:
                {
                    if (elev == 0)
                    {
                        printf(
                            "Elevator Controller announces "
                            "the elevator is at floor "
                        );

                        elev = 1;
                        elev2 = 1;

                        switch (msg.DATA[0])
                        {
                            case 0x5:
                                printf("1");
                                break;

                            case 0x6:
                                printf("2");
                                break;

                            case 0x7:
                                printf("3");
                                break;
                        }

                        printf(
                            "  - R ID:%4x LEN:%1x DATA:%02x \n",
                            static_cast<int>(msg.ID),
                            static_cast<int>(msg.LEN),
                            static_cast<int>(msg.DATA[0])
                        );
                    }

                    break;
                }


                case 0x0200:
                {
                    printf(
                        "Car Controller requested floor "
                    );

                    elev = 0;

                    switch (msg.DATA[0])
                    {
                        case 0x5:
                            printf("1");
                            break;

                        case 0x6:
                            printf("2");
                            break;

                        case 0x7:
                            printf("3");
                            break;
                    }

                    break;
                }


                case 0x0201:
                {
                    elev = 0;

                    printf(
                        "Floor 1 Controller made a request"
                    );

                    break;
                }


                case 0x0202:
                {
                    elev = 0;

                    printf(
                        "Floor 2 Controller made a request"
                    );

                    break;
                }


                case 0x0203:
                {
                    elev = 0;

                    printf(
                        "Floor 3 Controller made a request"
                    );

                    break;
                }
            }


            if (elev == 0)
            {
                printf(
                    "  - R ID:%4x LEN:%1x DATA:%02x \n",
                    static_cast<int>(msg.ID),
                    static_cast<int>(msg.LEN),
                    static_cast<int>(msg.DATA[0])
                );
            }


            // Remove the processed CAN message from the queue.
            canQueue.pop();

            i++;
        }
    }

    // Close the CAN receive channel.
    CAN_Close(h2);

    // Return the processed CAN message.
    return msg;
}