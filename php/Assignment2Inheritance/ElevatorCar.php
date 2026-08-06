<?php

require_once("Node.php");
require_once("LoggerTrait.php");

class ElevatorCar extends Node
{
    use LoggerTrait;

    private $currentFloor;

    public function __construct(
        $nodeID,
        $status,
        $currentFloor
    )
    {
        parent::__construct($nodeID, $status);
        $this->currentFloor = $currentFloor;
    }

    public function moveUp()
    {
        $this->currentFloor++;

        $this->logMessage(
            "Elevator moved up to floor "
            . $this->currentFloor
        );
    }

    public function moveDown()
    {
        $this->currentFloor--;

        $this->logMessage(
            "Elevator moved down to floor "
            . $this->currentFloor
        );
    }

    public function getCurrentFloor()
    {
        return $this->currentFloor;
    }
}

?>