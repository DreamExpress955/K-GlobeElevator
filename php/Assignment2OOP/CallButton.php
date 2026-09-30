<?php

/**
 * @file
 * @brief Defines a basic CallButton class for the elevator system.
 *
 * Represents an elevator call button with a direction. The class
 * provides functions for pressing the button and for getting or
 * changing the button's direction.
 */

/**
 * @class CallButton
 * @brief Represents an elevator call button and its direction.
 *
 * A CallButton stores the direction associated with the button,
 * such as "UP", "DOWN", or "EMERGENCY". The direction can be accessed
 * or updated using getter and setter methods.
 */
class CallButton
{
    /**
     * @var string
     * @brief Direction associated with the call button.
     */
    private $direction;

    /**
     * @brief Creates a new CallButton object.
     *
     * Initializes the call button with the specified direction.
     *
     * @param string $direction Direction associated with the button.
     */
    public function __construct($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @brief Simulates pressing the call button.
     *
     * Displays a message containing the direction associated with
     * the button.
     *
     * @return void
     */
    public function pressButton()
    {
        echo "Button pressed: "
            . $this->direction . "<br>";
    }

    /**
     * @brief Gets the direction of the call button.
     *
     * @return string The direction associated with the button.
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @brief Sets the direction of the call button.
     *
     * @param string $direction New direction to assign to the button.
     *
     * @return void
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}

?>