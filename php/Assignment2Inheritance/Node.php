<?php

class Node
{
    private $nodeID;
    private $status;

    public function __construct($nodeID, $status)
    {
        $this->nodeID = $nodeID;
        $this->status = $status;
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
}

?>