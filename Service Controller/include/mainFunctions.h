/**
 * @file mainFunctions.h
 * @brief Declares utility functions used by the elevator Service Controller.
 *
 * Provides functions for displaying the Service Controller menu,
 * selecting CAN node and message IDs, and converting elevator floor
 * numbers between integer and hexadecimal representations.
 */

#ifndef MAIN_FUNCTIONS
#define MAIN_FUNCTIONS

/**
 * @brief Displays the Service Controller menu and gets a user selection.
 *
 * @return The menu option selected by the user.
 */
int menu();

/**
 * @brief Allows the user to select a CAN node ID.
 *
 * @return The CAN node ID selected by the user.
 */
int chooseID();

/**
 * @brief Allows the user to select a CAN message.
 *
 * @return The CAN message selection made by the user.
 */
int chooseMsg();

/**
 * @brief Converts an elevator floor number to its hexadecimal value.
 *
 * @param floorVal Elevator floor number to convert.
 *
 * @return The hexadecimal representation associated with the floor.
 */
int HexFromFloor(int floorVal);

/**
 * @brief Converts a hexadecimal floor value to an elevator floor number.
 *
 * @param Hex Hexadecimal floor value to convert.
 *
 * @return The elevator floor number associated with the hexadecimal value.
 */
int FloorFromHex(int Hex);

#endif