<?php

require_once("Node.php");
require_once("Activatable.php");
require_once("LoggerTrait.php");

class DistanceSensor extends Node implements Activatable
{
    use LoggerTrait;

    private $distance;

    public function __construct(
        $nodeID,
        $status,
        $distance
    )
    {
        parent::__construct($nodeID, $status);

        $this->distance = $distance;
    }

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