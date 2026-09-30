/**
 * @file databaseFunctions.h
 * @brief Database functions for the K-Globe Elevator system.
 *
 * Declares functions used to communicate with the Elevator MySQL database.
 * These functions allow the program to read and update the elevator's
 * current floor and log CAN network messages.
 */

#ifndef DB_FUNCTIONS
#define DB_FUNCTIONS

#include <cstdint>

/**
 * @brief Gets the elevator's current floor from the database.
 *
 * Reads the currentFloor value from the elevatorNetwork table
 * for nodeID 1.
 *
 * @return The current floor number stored in the database.
 */
int db_getFloorNum();

/**
 * @brief Updates the elevator's current floor in the database.
 *
 * Updates the currentFloor value in the elevatorNetwork table
 * for nodeID 1.
 *
 * @param floorNum The new floor number to store in the database.
 */
void db_setFloorNum(int floorNum);

/**
 * @brief Logs a CAN message to the elevator database.
 *
 * Stores a CAN message in the CANNetwork table, including the node ID,
 * message ID, data length, message data, and a description of the message.
 *
 * @param nodeID ID of the CAN node associated with the message.
 * @param messageID CAN message identifier.
 * @param dataLength Number of bytes in the CAN message.
 * @param data Pointer to the CAN message data.
 * @param description Description of the CAN message.
 */
void db_logCANMessage(
    int nodeID,
    int messageID,
    int dataLength,
    uint8_t* data,
    const char* description
);

#endif
