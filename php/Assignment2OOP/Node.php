<?php

/**
 * @file
 * @brief Defines the Node class for the OOP elevator system.
 *
 * Represents a node in the elevator network. Each node stores information
 * including its node ID, status, current floor, requested floor, and
 * additional information. The class also provides database access and
 * keeps track of the total number of Node objects created.
 */

require_once("Database.php");

/**
 * @class Node
 * @brief Represents a node in the elevator network.
 *
 * The Node class stores information associated with an elevator network
 * node and provides getter and setter methods for accessing and modifying
 * its properties.
 *
 * The class also maintains a static count of the number of Node objects
 * created and provides access to the elevator database.
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
     * @var int
     * @brief Current floor associated with the node.
     */
    private $currentFloor;

    /**
     * @var int
     * @brief Floor currently requested by the node.
     */
    private $requestedFloor;

    /**
     * @var mixed
     * @brief Additional information associated with the node.
     */
    private $otherInfo;

    /**
     * @var int
     * @brief Number of Node objects that have been created.
     *
     * The count is increased each time the Node constructor is called.
     */
    protected static $nodeCount = 0;

    /**
     * @brief Creates a new Node object.
     *
     * Initializes the node information and increases the total
     * number of Node objects created.
     *
     * @param int $nodeID Unique ID assigned to the node.
     * @param mixed $status Initial status of the node.
     * @param int $currentFloor Current floor of the node.
     * @param int $requestedFloor Floor requested by the node.
     * @param mixed $otherInfo Additional information associated with the node.
     */
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

    /**
     * @brief Creates a connection to the elevator database.
     *
     * Uses the static Database::connect() method to establish and
     * return a MySQL database connection.
     *
     * @return mysqli The MySQL database connection object.
     */
    public function dbConnect()
    {
        return Database::connect();
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
     * @brief Sets the node status.
     *
     * @param mixed $status New status to assign to the node.
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @brief Gets the current floor.
     *
     * @return int The current floor associated with the node.
     */
    public function getCurrentFloor()
    {
        return $this->currentFloor;
    }

    /**
     * @brief Sets the current floor.
     *
     * @param int $floor New current floor for the node.
     *
     * @return void
     */
    public function setCurrentFloor($floor)
    {
        $this->currentFloor = $floor;
    }

    /**
     * @brief Gets the requested floor.
     *
     * @return int The floor currently requested by the node.
     */
    public function getRequestedFloor()
    {
        return $this->requestedFloor;
    }

    /**
     * @brief Sets the requested floor.
     *
     * @param int $floor New requested floor for the node.
     *
     * @return void
     */
    public function setRequestedFloor($floor)
    {
        $this->requestedFloor = $floor;
    }

    /**
     * @brief Gets the additional information associated with the node.
     *
     * @return mixed Additional information associated with the node.
     */
    public function getOtherInfo()
    {
        return $this->otherInfo;
    }

    /**
     * @brief Sets the additional information associated with the node.
     *
     * @param mixed $info New additional information to store for the node.
     *
     * @return void
     */
    public function setOtherInfo($info)
    {
        $this->otherInfo = $info;
    }

    /**
     * @brief Gets the total number of Node objects created.
     *
     * Returns the shared node counter. The counter is increased each
     * time a new Node object is created.
     *
     * @return int Total number of Node objects created.
     */
    public static function getNodeCount()
    {
        return self::$nodeCount;
    }
}

?>