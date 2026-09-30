<?php

/**
 * @file
 * @brief Defines the CallButton class for the elevator system.
 *
 * Represents an elevator call button that can be activated to request
 * elevator service in a specific direction. The class inherits common
 * node information from Node, implements the Activatable interface,
 * and uses LoggerTrait for message logging.
 */

require_once("Node.php");
require_once("Activatable.php");
require_once("LoggerTrait.php");

/**
 * @class CallButton
 * @brief Represents an elevator call button.
 *
 * A CallButton is an elevator network node that stores a direction
 * and can be activated. When activated, the button logs a message
 * containing the requested direction.
 *
 * The class extends Node to inherit common node properties and implements
 * Activatable to provide standard activation behavior.
 */
class CallButton extends Node implements Activatable
{
    use LoggerTrait;

    /**
     * @var string
     * @brief Direction associated with the call button.
     *
     * Stores the direction requested by the button, such as
     * "Up" or "Down".
     */
    private $direction;

    /**
     * @brief Creates a new CallButton object.
     *
     * Initializes the node information using the parent Node constructor
     * and stores the direction associated with the call button.
     *
     * @param int $nodeID Unique ID of the call button node.
     * @param mixed $status Current status of the node.
     * @param string $direction Direction associated with the call button.
     */
    public function __construct(
        $nodeID,
        $status,
        $direction
    )
    {
        parent::__construct($nodeID, $status);

        $this->direction = $direction;
    }

    /**
     * @brief Activates the elevator call button.
     *
     * Logs a message indicating that the call button was pressed
     * along with the direction associated with the button.
     *
     * @return void
     */
    public function activate()
    {
        $this->logMessage(
            "Call Button Pressed: "
            . $this->direction
        );
    }
}

?>
``