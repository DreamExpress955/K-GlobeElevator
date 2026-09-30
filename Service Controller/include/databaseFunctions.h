/**
 * @file databaseFunctions.h
 * @brief Declares database functions used by the elevator Service Controller.
 *
 * Provides functions for communicating with the Elevator database.
 * The Service Controller can use these functions to read and update
 * elevator floor information, process website requests, log CAN
 * messages, update the elevator door status, and read the elevator
 * stop flag.
 */

#ifndef DB_FUNCTIONS
#define DB_FUNCTIONS

#include <cstdint>

/**
 * @brief Gets the elevator's current floor from the database.
 *
 * @return The current floor number stored in the database.
 */
int db_getFloorNum();

/**
 * @brief Updates the elevator's current floor in the database.
 *
 * @param floorNum New floor number to store in the database.
 *
 * @return Result of the floor update operation.
 */
int db_setFloorNum(int floorNum);

/**
 * @brief Logs a CAN message to the elevator database.
 *
 * Stores information about a CAN network message, including the node ID,
 * message ID, data length, payload, and message description.
 *
 * @param nodeID ID of the CAN node associated with the message.
 * @param messageID CAN message identifier.
 * @param dataLength Number of bytes contained in the CAN message.
 * @param data Pointer to the CAN message data.
 * @param description Description associated with the CAN message.
 *
 * @return void
 */
void db_logCANMessage(
    int nodeID,
    int messageID,
    int dataLength,
    uint8_t* data,
    const char* description
);

/**
 * @brief Gets the floor requested through the website.
 *
 * @return The requested floor number stored in the database.
 */
int db_getRequestedFloor();

/**
 * @brief Gets the type of floor request stored in the database.
 *
 * The request type identifies where the elevator request originated.
 *
 * @return The stored request type.
 */
int db_getRequestType();

/**
 * @brief Clears the current website floor request.
 *
 * Clears or resets the website request after the Service Controller
 * has processed the request.
 *
 * @return Result of the clear request operation.
 */
int db_clearWebsiteRequest();

/**
 * @brief Updates the elevator door status in the database.
 *
 * @param door Door status value to store in the database.
 *
 * @return void
 */
void db_updateDoor(int door);

/**
 * @brief Gets the elevator stop flag from the database.
 *
 * The stop flag is used by the Service Controller to determine the
 * operating state requested through the elevator system.
 *
 * @return The current stop flag value.
 */
int db_getStopFlag();

#endif