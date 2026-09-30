<?php

/**
 * @file
 * @brief Defines the Activatable interface.
 *
 * Provides a common interface for elevator system components that
 * can be activated.
 */

/**
 * @interface Activatable
 * @brief Defines a common activation method for elevator components.
 *
 * Any class that implements this interface must provide its own
 * implementation of the activate() method.
 */
interface Activatable
{
    /**
     * @brief Activates the component.
     *
     * Classes that implement the Activatable interface use this method
     * to define the actions that occur when the component is activated.
     *
     * @return void
     */
    public function activate();
}

?>