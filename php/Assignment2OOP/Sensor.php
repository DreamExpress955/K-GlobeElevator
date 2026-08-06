<?php

class Sensor
{
    private $distance;

    public function __construct($distance)
    {
        $this->distance = $distance;
    }

    public function readDistance()
    {
        return $this->distance;
    }

    public function setDistance($distance)
    {
        $this->distance = $distance;
    }
}

?>
