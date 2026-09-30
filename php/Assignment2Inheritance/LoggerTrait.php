<?php

/**
 * @file
 * @brief Defines the LoggerTrait used by elevator system components.
 *
 * Provides a reusable logging function that allows elevator components
 * to display log messages in a consistent format.
 */

/**
 * @trait LoggerTrait
 * @brief Provides basic logging functionality for elevator components.
 *
 * Classes that use this trait can call logMessage() to display
 * formatted log messages on the webpage.
 */
trait LoggerTrait
{
    /**
     * @brief Displays a formatted log message.
     *
     * Adds a "[LOG]" prefix to the supplied message and displays
     * the result on the webpage followed by a line break.
     *
     * @param string $message The message to display in the log.
     *
     * @return void
     */
    public function logMessage($message)
    {
        echo "[LOG] " . $message . "<br>";
    }
}

?>