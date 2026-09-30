<?php

/**
 * @file
 * @brief Defines the FloorNode class for the OOP elevator system.
 *
 * Represents a floor in the elevator system. Each FloorNode stores
 * a floor number and provides functionality for calling the elevator
 * from that floor.
 */

/**
 * @class FloorNode
 * @brief Represents a floor node in the elevator system.
 *
 * The FloorNode class stores the floor number associated with a
 * particular floor. The floor number is kept private and can be
 * accessed or modified using getter and setter methods.
 */
class FloorNode
{
    /**
     * @var int
     * @brief Floor number associated with this node.
     */
    private $floorNumber;

    /**
     * @brief Creates a new FloorNode object.
     *
     * Initializes the floor node with the specified floor number.
     *
     * @param int $floorNumber Floor number assigned to the node.
     */
    public function __construct($floorNumber)
    {
        $this->floorNumber = $floorNumber;
    }

    /**
     * @brief Calls the elevator from this floor.
     *
     * Displays a message indicating the floor from which the elevator
     * has been called.
     *
     * @return void
     */
    public function callElevator()
    {
        echo "Elevator called from floor "
            . $this->floorNumber . "<br>";
    }

    /**
     * @brief Gets the floor number.
     *
     * @return int The floor number associated with this node.
     */
    public function getFloorNumber()
    {
        return $this->floorNumber;
    }

    /**
     * @brief Sets the floor number.
     *
     * @param int $number New floor number to assign to the node.
     *
     * @return void
     */
    public function setFloorNumber($number)
    {
        $this->floorNumber = $number;
    }
}

?>