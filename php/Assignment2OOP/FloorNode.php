<?php

class FloorNode
{
    private $floorNumber;

    public function __construct($floorNumber)
    {
        $this->floorNumber = $floorNumber;
    }

    public function callElevator()
    {
        echo "Elevator called from floor "
            . $this->floorNumber . "<br>";
    }

    public function getFloorNumber()
    {
        return $this->floorNumber;
    }

    public function setFloorNumber($number)
    {
        $this->floorNumber = $number;
    }
}

?>