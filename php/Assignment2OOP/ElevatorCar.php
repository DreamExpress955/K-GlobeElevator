<?php

require_once("Node.php");

class ElevatorCar
{
    private $carID;
    private $currentFloor;
    private $direction;

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

    public function moveUp()
    {
        $this->currentFloor++;
    }

    public function moveDown()
    {
        $this->currentFloor--;
    }

    public function getCurrentFloor()
    {
        return $this->currentFloor;
    }

    public function setCurrentFloor($floor)
    {
        $this->currentFloor = $floor;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}

?>