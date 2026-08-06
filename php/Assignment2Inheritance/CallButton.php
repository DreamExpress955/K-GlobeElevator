<?php

require_once("Node.php");
require_once("Activatable.php");
require_once("LoggerTrait.php");

class CallButton extends Node implements Activatable
{
    use LoggerTrait;

    private $direction;

    public function __construct(
        $nodeID,
        $status,
        $direction
    )
    {
        parent::__construct($nodeID, $status);

        $this->direction = $direction;
    }

    public function activate()
    {
        $this->logMessage(
            "Call Button Pressed: "
            . $this->direction
        );
    }
}

?>