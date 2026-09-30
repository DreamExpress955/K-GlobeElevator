/**
 * @file pcanFunctions.h
 * @brief Declares PCAN communication functions for the elevator Service Controller.
 *
 * Provides the CAN communication constants and functions used by the
 * K-Globe Elevator Service Controller. This file defines the CAN message
 * IDs used by each elevator node, floor request command values, PCAN
 * status values, and functions for transmitting and receiving CAN messages.
 */

#ifndef PCAN_FUNCTIONS
#define PCAN_FUNCTIONS

#include <libpcan.h>


/* ============================================================
 * PCAN Status Codes
 * ============================================================ */

/**
 * @brief PCAN status value indicating that the receive queue is empty.
 */
#define PCAN_RECEIVE_QUEUE_EMPTY 0x00020U

/**
 * @brief PCAN status value indicating that no error has occurred.
 */
#define PCAN_NO_ERROR 0x00000U


/* ============================================================
 * Elevator CAN Message IDs
 * ============================================================ */

/**
 * @brief CAN message ID for messages from the Supervisory Controller
 *        to the Elevator Controller.
 */
#define ID_SC_TO_EC 0x100

/**
 * @brief CAN message ID for messages from the Elevator Controller
 *        to all other elevator network nodes.
 */
#define ID_EC_TO_ALL 0x101

/**
 * @brief CAN message ID for messages from the Car Controller
 *        to the Supervisory Controller.
 */
#define ID_CC_TO_SC 0x200

/**
 * @brief CAN message ID for messages from the Floor 1 Controller
 *        to the Supervisory Controller.
 */
#define ID_F1_TO_SC 0x201

/**
 * @brief CAN message ID for messages from the Floor 2 Controller
 *        to the Supervisory Controller.
 */
#define ID_F2_TO_SC 0x202

/**
 * @brief CAN message ID for messages from the Floor 3 Controller
 *        to the Supervisory Controller.
 */
#define ID_F3_TO_SC 0x203

/**
 * @brief CAN message ID for requests originating from the website.
 */
#define ID_WEBSITE 0x300


/* ============================================================
 * Elevator Floor Commands
 * ============================================================ */

/**
 * @brief CAN command used to request Floor 1.
 */
#define GO_TO_FLOOR1 0x05

/**
 * @brief CAN command used to request Floor 2.
 */
#define GO_TO_FLOOR2 0x06

/**
 * @brief CAN command used to request Floor 3.
 */
#define GO_TO_FLOOR3 0x07


/* ============================================================
 * PCAN Communication Functions
 * ============================================================ */

/**
 * @brief Transmits a CAN message through the PCAN interface.
 *
 * Sends a CAN message containing the specified CAN message ID
 * and data value.
 *
 * @param id CAN message identifier.
 * @param data Data value to transmit.
 *
 * @return Result of the PCAN transmission operation.
 */
int pcanTx(int id, int data);

/**
 * @brief Receives CAN messages through the PCAN interface.
 *
 * Attempts to receive the specified number of CAN messages from
 * the PCAN receive queue.
 *
 * @param num_msgs Number