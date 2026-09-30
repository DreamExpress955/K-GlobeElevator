<?php

/**
 * @file
 * @brief Defines the ElevatorCar class for the OOP elevator system.
 *
 * Represents an elevator car and stores information about the car's
 * ID, current floor, and direction. The class provides methods for
 * moving the elevator and accessing or updating its properties.
 */

require_once("Node.php");

/**
 * @class ElevatorCar
 * @brief Represents an elevator car in the elevator system.
 *
 * The ElevatorCar class stores the car ID, current floor, and current
 * direction. Private properties are accessed and modified through
 * class methods to demonstrate encapsulation.
 */
class ElevatorCar
{
    /**
     * @var int
     * @brief Unique ID assigned to the elevator car.
     */
    private $carID;

    /**
     * @var int
     * @brief Current floor of the elevator car.
     */
    private $currentFloor;

    /**
     * @var string
     * @brief Current direction of the elevator car.
     */
    private $direction;

    /**
     * @brief Creates a new ElevatorCar object.
     *
     * Initializes the elevator with a car ID, starting floor,
     * and direction.
     *
     * @param int $carID Unique ID assigned to the elevator car.
     * @param int $currentFloor Starting floor of the elevator.
     * @param string $direction Starting direction of the elevator.
     */
    public function __construct(
        $carID,
        $currentFloor,
        $direction
    )
    {
        $this->carID = $carID;
        $this->currentFloor = $currentFloor;
        $this->direction = $direction;
    }

    /**
     * @brief Moves the elevator up one floor.
     *
     * Increases the current floor number by one.
     *
     * @return void
     */
    public function moveUp()
    {
        $this->currentFloor++;
    }

    /**
     * @brief Moves the elevator down one floor.
     *
     * Decreases the current floor number by one.
     *
     * @return void
     */
    public function moveDown()
    {
        $this->currentFloor--;
    }

    /**
     * @brief Gets the elevator's current floor.
     *
     * @return int The current floor of the elevator.
     */
    public function getCurrentFloor()
    {
        return $this->currentFloor;
    }

    /**
     * @brief Sets the elevator's current floor.
     *
     * @param int $floor New floor to assign to the elevator.
     *
     * @return void
     */
    public function setCurrentFloor($floor)
    {
        $this->currentFloor = $floor;
    }

    /**
     * @brief Gets the elevator's current direction.
     *
     * @return string The current direction of the elevator.
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @brief Sets the elevator's current direction.
     *
     * @param string $direction New direction to assign to the elevator.
     *
     * @return void
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}

?>