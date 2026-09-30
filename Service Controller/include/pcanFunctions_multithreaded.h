/**
 * @file pcanFunctionsMultithreaded.h
 * @brief Declares multithreaded PCAN communication functions for the
 *        elevator Service Controller.
 *
 * Provides CAN communication functions used by the K-Globe Elevator
 * Service Controller. The file defines CAN message IDs, elevator floor
 * command values, PCAN status values, and functions for transmitting
 * and receiving CAN messages using multithreaded processing.
 */

#ifndef PCAN_FUNCTIONS_MULTITHREADED_H
#define PCAN_FUNCTIONS_MULTITHREADED_H

#include <libpcan.h>
#include <string>

/**
 * @brief PCAN status value indicating that the receive queue is empty.
 */
#define PCAN_RECEIVE_QUEUE_EMPTY 0x00020U

/**
 * @brief PCAN status value indicating that no error has occurred.
 */
#define PCAN_NO_ERROR 0x00000U


/* ============================================================
 * CAN Message IDs
 * ============================================================ */

/**
 * @brief CAN message ID for communication from the Service Controller
 *        to the Elevator Controller.
 */
#define ID_SC_TO_EC 0x100

/**
 * @brief CAN message ID for communication from the Elevator Controller
 *        to all nodes.
 */
#define ID_EC_TO_ALL 0x101

/**
 * @brief CAN message ID for communication from the Car Controller
 *        to the Service Controller.
 */
#define ID_CC_TO_SC 0x200

/**
 * @brief CAN message ID for Floor 1 communication to the
 *        Service Controller.
 */
#define ID_F1_TO_SC 0x201

/**
 * @brief CAN message ID for Floor 2 communication to the
 *        Service Controller.
 */
#define ID_F2_TO_SC 0x202

/**
 * @brief CAN message ID for Floor 3 communication to the
 *        Service Controller.
 */
#define ID_F3_TO_SC 0x203

/**
 * @brief CAN message ID used for requests originating from the website.
 */
#define ID_WEBSITE 0x300


/* ============================================================
 * Elevator Floor Commands
 * ============================================================ */

/**
 * @brief CAN command value used to request Floor 1.
 */
#define GO_TO_FLOOR1 0x05

/**
 * @brief CAN command value used to request Floor 2.
 */
#define GO_TO_FLOOR2 0x06

/**
 * @brief CAN command value used to request Floor 3.
 */
#define GO_TO_FLOOR3 0x07


/* ============================================================
 * PCAN Communication Functions
 * ============================================================ */

/**
 * @brief Transmits a CAN message through the PCAN interface.
 *
 * Sends a CAN message containing the supplied CAN ID and data value.
 * A description is also provided so the transmitted message can be
 * identified or logged by the Service Controller.
 *
 * @param id CAN message identifier.
 * @param data Data value to transmit in the CAN message.
 * @param description Description associated with the CAN transmission.
 *
 * @return Result of the PCAN transmission operation.
 */
int pcanTx(
    int id,
    int data,
    std::string description
);

/**
 * @brief Starts multithreaded PCAN message reception.
 *
 * Receives CAN messages and processes the received message details
 * using the Service Controller's multithreaded receive system.
 *
 * @return void
 */
void pcanRxWithDetailsMultithreaded();

/**
 * @brief Stops the PCAN receive threads.
 *
 * Signals the active PCAN receive threads to stop processing
 * incoming CAN messages.
 *
 * @return void
 */
void stopPcanRxThreads();

/**
 * @brief Stops the multithreaded PCAN communication system.
 *
 * Stops the PCAN multithreaded communication operation and allows
 * the Service Controller to shut down PCAN processing.
 *
 * @return void
 */
void stopPcanMultithreaded();

/**
 * @brief Checks whether the PCAN communication system is busy.
 *
 * @return true if the PCAN system is currently busy.
 * @return false if the PCAN system is available.
 */
bool isPCANBusy();

#endif