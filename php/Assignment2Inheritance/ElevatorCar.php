<?php

/**
 * @file
 * @brief Defines the ElevatorCar class for the elevator system.
 *
 * Represents the elevator car and keeps track of its current floor.
 * The elevator can move up or down one floor at a time and logs
 * each movement using LoggerTrait.
 */

require_once("Node.php");
require_once("LoggerTrait.php");

/**
 * @class ElevatorCar
 * @brief Represents the elevator car within the elevator system.
 *
 * The ElevatorCar stores the current floor of the elevator and provides
 * functions for moving the elevator up or down. Each movement is logged
 * using LoggerTrait.
 *
 * The class extends Node to inherit common node information.
 */
class ElevatorCar extends Node
{
    use LoggerTrait;

    /**
     * @var int
     * @brief Current floor of the elevator.
     */
    private $currentFloor;

    /**
     * @brief Creates a new ElevatorCar object.
     *
     * Initializes the common node information using the parent Node
     * constructor and sets the elevator's starting floor.
     *
     * @param int $nodeID Unique ID of the elevator node.
     * @param mixed $status Current status of the elevator node.
     * @param int $currentFloor Starting floor of the elevator.
     */
    public function __construct(
        $nodeID,
        $status,
        $currentFloor
    )
    {
        parent::__construct($nodeID, $status);

        $this->currentFloor = $currentFloor;
    }

    /**
     * @brief Moves the elevator up one floor.
     *
     * Increases the current floor by one and logs the new floor.
     *
     * @return void
     */
    public function moveUp()
    {
        $this->currentFloor++;

        $this->logMessage(
            "Elevator moved up to floor "
            . $this->currentFloor
        );
    }

    /**
     * @brief Moves the elevator down one floor.
     *
     * Decreases the current floor by one and logs the new floor.
     *
     * @return void
     */
    public function moveDown()
    {
        $this->currentFloor--;

        $this->logMessage(
            "Elevator moved down to floor "
            . $this->currentFloor
        );
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
}

?>