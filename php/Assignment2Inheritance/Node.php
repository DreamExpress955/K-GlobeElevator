<?php

/**
 * @file
 * @brief Defines the base Node class for the elevator system.
 *
 * Provides common properties and functions used by elevator network
 * components. Each node has a unique node ID and a status that can
 * be accessed or updated using getter and setter methods.
 */

/**
 * @class Node
 * @brief Base class for nodes in the elevator system.
 *
 * The Node class stores information that is common to elevator system
 * components, including a node ID and status.
 *
 * Other elevator components such as ElevatorCar, CallButton, and
 * DistanceSensor inherit from this class.
 */
class Node
{
    /**
     * @var int
     * @brief Unique ID assigned to the node.
     */
    private $nodeID;

    /**
     * @var mixed
     * @brief Current status of the node.
     */
    private $status;

    /**
     * @brief Creates a new Node object.
     *
     * Initializes the node with an ID and status.
     *
     * @param int $nodeID Unique ID assigned to the node.
     * @param mixed $status Initial status of the node.
     */
    public function __construct($nodeID, $status)
    {
        $this->nodeID = $nodeID;
        $this->status = $status;
    }

    /**
     * @brief Gets the node ID.
     *
     * @return int The unique ID assigned to the node.
     */
    public function getNodeID()
    {
        return $this->nodeID;
    }

    /**
     * @brief Sets the node ID.
     *
     * @param int $nodeID New ID to assign to the node.
     *
     * @return void
     */
    public function setNodeID($nodeID)
    {
        $this->nodeID = $nodeID;
    }

    /**
     * @brief Gets the current node status.
     *
     * @return mixed The current status of the node.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @brief Sets the current node status.
     *
     * @param mixed $status New status to assign to the node.
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}

?>