<?php

/**
 * @file
 * @brief Contains the ElevatorController class for testing elevator operations.
 *
 * Defines basic elevator controller functions used to validate floor requests,
 * network communication, sensor readings, and CAN node IDs. Exceptions are
 * thrown when invalid values or communication problems are detected.
 */

require_once("ErrorExceptions.php");

/**
 * @class ElevatorController
 * @brief Provides basic validation and control functions for the elevator.
 *
 * The ElevatorController validates several parts of the elevator system,
 * including requested floors, network communication, sensor readings,
 * and CAN node IDs.
 */
class ElevatorController
{
    /**
     * @var int
     * @brief Maximum floor that can be requested.
     */
    private $maxFloor = 3;

    /**
     * @brief Requests that the elevator move to a specified floor.
     *
     * Checks that the requested floor is within the valid range of
     * floor 1 to the maximum floor before accepting the request.
     *
     * @param int $floor The floor that the elevator should move to.
     *
     * @throws InvalidFloorException If the requested floor is outside
     * the valid floor range.
     */
    public function requestFloor($floor)
    {
        if ($floor < 1 || $floor > $this->maxFloor)
        {
            throw new InvalidFloorException(
                "Requested floor {$floor} does not exist."
            );
        }

        echo "Moving elevator to floor {$floor}<br>";
    }

    /**
     * @brief Checks the status of the CAN network connection.
     *
     * Determines whether communication with the elevator network is
     * currently available.
     *
     * @param bool $connected True if the network is connected, false otherwise.
     *
     * @throws CommunicationException If network communication is unavailable.
     */
    public function checkConnection($connected)
    {
        if (!$connected)
        {
            throw new CommunicationException(
                "CAN Network Communication Failure."
            );
        }

        echo "Network Connection OK<br>";
    }

    /**
     * @brief Validates a sensor reading.
     *
     * Checks that the supplied distance measurement is valid. Negative
     * distance values are considered invalid sensor readings.
     *
     * @param float $distance Distance value reported by the sensor.
     *
     * @throws SensorException If the sensor returns a negative distance.
     */
    public function readSensor($distance)
    {
        if ($distance < 0)
        {
            throw new SensorException(
                "Invalid sensor value detected."
            );
        }

        echo "Sensor Reading = {$distance}<br>";
    }

    /**
     * @brief Validates a CAN network node ID.
     *
     * Checks that the supplied node ID is greater than zero before
     * accepting the node as valid.
     *
     * @param int $nodeID CAN network node ID to validate.
     *
     * @throws NodeException If the node ID is zero or negative.
     */
    public function validateNode($nodeID)
    {
        if ($nodeID <= 0)
        {
            throw new NodeException(
                "Invalid Node ID."
            );
        }

        echo "Node {$nodeID} Valid<br>";
    }
}

?>