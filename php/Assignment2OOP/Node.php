<?php

require_once("Database.php");

class Node
{
    private $nodeID;
    private $status;
    private $currentFloor;
    private $requestedFloor;
    private $otherInfo;

    protected static $nodeCount = 0;

    public function __construct(
        $nodeID,
        $status,
        $currentFloor,
        $requestedFloor,
        $otherInfo
    )
    {
        $this->nodeID = $nodeID;
        $this->status = $status;
        $this->currentFloor = $currentFloor;
        $this->requestedFloor = $requestedFloor;
        $this->otherInfo = $otherInfo;

        self::$nodeCount++;
    }

    public function dbConnect()
    {
        return Database::connect();
    }

    public function getNodeID()
    {
        return $this->nodeID;
    }

    public function setNodeID($nodeID)
    {
        $this->nodeID = $nodeID;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getCurrentFloor()
    {
        return $this->currentFloor;
    }

    public function setCurrentFloor($floor)
    {
        $this->currentFloor = $floor;
    }

    public function getRequestedFloor()
    {
        return $this->requestedFloor;
    }

    public function setRequestedFloor($floor)
    {
        $this->requestedFloor = $floor;
    }

    public function getOtherInfo()
    {
        return $this->otherInfo;
    }

    public function setOtherInfo($info)
    {
        $this->otherInfo = $info;
    }

    public static function getNodeCount()
    {
        return self::$nodeCount;
    }
}

?>