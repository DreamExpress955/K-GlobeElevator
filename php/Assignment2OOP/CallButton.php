<?php

class CallButton
{
    private $direction;

    public function __construct($direction)
    {
        $this->direction = $direction;
    }

    public function pressButton()
    {
        echo "Button pressed: "
            . $this->direction . "<br>";
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}

?>