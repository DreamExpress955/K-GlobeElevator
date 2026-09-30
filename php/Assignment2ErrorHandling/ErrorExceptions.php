<?php

/**
 * @file
 * @brief Defines custom exceptions used by the elevator control system.
 *
 * Contains exception classes for handling invalid floor requests,
 * communication failures, invalid sensor readings, and invalid
 * CAN network node IDs.
 */

/**
 * @class InvalidFloorException
 * @brief Exception thrown when an invalid elevator floor is requested.
 *
 * Used when a requested floor is outside the valid floor range
 * supported by the elevator.
 */
class InvalidFloorException extends Exception
{
}

/**
 * @class CommunicationException
 * @brief Exception thrown when network communication fails.
 *
 * Used when communication with the elevator CAN network is
 * unavailable or cannot be established.
 */
class CommunicationException extends Exception
{
}

/**
 * @class SensorException
 * @brief Exception thrown when an invalid sensor reading is detected.
 *
 * Used when a sensor returns a value that is considered invalid
 * by the elevator control system.
 */
class SensorException extends Exception
{
}

/**
 * @class NodeException
 * @brief Exception thrown when an invalid CAN node ID is detected.
 *
 * Used when a CAN network node ID does not meet the requirements
 * of the elevator control system.
 */
class NodeException extends Exception
{
}

?>