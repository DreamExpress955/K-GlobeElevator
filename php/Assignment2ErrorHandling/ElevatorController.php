<?php

require_once("ErrorExceptions.php");


class ElevatorController
{
    private $maxFloor = 3;

    public function requestFloor($floor)
    {
        if ($floor < 1 || $floor > $this->maxFloor)
        {
            throw new InvalidFloorException(
                "Requested floor {$floor} does not exist."
            );
        }

        echo "Moving elevator to floor {$floor}<br>";
    }

    public function checkConnection($connected)
    {
        if (!$connected)
        {
            throw new CommunicationException(
                "CAN Network Communication Failure."
            );
        }

        echo "Network Connection OK<br>";
    }

    public function readSensor($distance)
    {
        if ($distance < 0)
        {
            throw new SensorException(
                "Invalid sensor value detected."
            );
        }

        echo "Sensor Reading = {$distance}<br>";
    }

    public function validateNode($nodeID)
    {
        if ($nodeID <= 0)
        {
            throw new NodeException(
                "Invalid Node ID."
            );
        }

        echo "Node {$nodeID} Valid<br>";
    }
}

?>