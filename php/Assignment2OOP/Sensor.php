<?php

/**
 * @file
 * @brief Defines the Sensor class for the OOP elevator system.
 *
 * Represents a sensor used by the elevator system to store a distance
 * measurement. The distance value can be read or updated using the
 * class methods.
 */

/**
 * @class Sensor
 * @brief Represents a distance sensor in the elevator system.
 *
 * The Sensor class stores a distance measurement as a private property.
 * The measurement can be accessed using readDistance() and updated
 * using setDistance().
 *
 * This class demonstrates encapsulation by keeping the distance value
 * private and providing public methods to access and modify it.
 */
class Sensor
{
    /**
     * @var mixed
     * @brief Current distance measurement of the sensor.
     */
    private $distance;

    /**
     * @brief Creates a new Sensor object.
     *
     * Initializes the sensor with the specified distance measurement.
     *
     * @param mixed $distance Initial distance measurement of the sensor.
     */
    public function __construct($distance)
    {
        $this->distance = $distance;
    }

    /**
     * @brief Gets the current distance measurement.
     *
     * @return mixed The current distance measured by the sensor.
     */
    public function readDistance()
    {
        return $this->distance;
    }

    /**
     * @brief Sets the sensor's distance measurement.
     *
     * Updates the stored distance value with a new measurement.
     *
     * @param mixed $distance New distance measurement to store.
     *
     * @return void
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }
}

?>