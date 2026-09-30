<?php

/**
 * @file
 * @brief Defines the DistanceSensor class for the elevator system.
 *
 * Represents a distance sensor connected to the elevator network.
 * The sensor stores a distance measurement and can be activated to
 * log the current sensor reading.
 */

require_once("Node.php");
require_once("Activatable.php");
require_once("LoggerTrait.php");

/**
 * @class DistanceSensor
 * @brief Represents a distance sensor in the elevator system.
 *
 * A DistanceSensor is an elevator network node that stores a distance
 * measurement. When activated, the sensor logs its current reading
 * in centimeters.
 *
 * The class extends Node to inherit common node properties, implements
 * Activatable to provide activation functionality, and uses LoggerTrait
 * for message logging.
 */
class DistanceSensor extends Node implements Activatable
{
    use LoggerTrait;

    /**
     * @var mixed
     * @brief Current distance measured by the sensor.
     *
     * Stores the distance measurement that will be reported when
     * the sensor is activated.
     */
    private $distance;

    /**
     * @brief Creates a new DistanceSensor object.
     *
     * Initializes the common node information using the parent Node
     * constructor and stores the initial distance measurement.
     *
     * @param int $nodeID Unique ID of the distance sensor node.
     * @param mixed $status Current status of the sensor node.
     * @param mixed $distance Initial distance measurement in centimeters.
     */
    public function __construct(
        $nodeID,
        $status,
        $distance
    )
    {
        parent::__construct($nodeID, $status);

        $this->distance = $distance;
    }

    /**
     * @brief Activates the distance sensor.
     *
     * Logs the sensor's current distance measurement in centimeters
     * using LoggerTrait.
     *
     * @return void
     */
    public function activate()
    {
        $this->logMessage(
            "Sensor Reading: "
            . $this->distance
            . " cm"
        );
    }
}

?>