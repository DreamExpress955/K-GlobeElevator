<?php

/**
 * @file
 * @brief Tests the ElevatorController and its exception handling.
 *
 * Creates an ElevatorController object and tests the validation functions
 * for CAN nodes, network communication, sensor readings, and floor requests.
 *
 * Each type of elevator error is handled using its corresponding custom
 * exception. A general Exception catch is included to handle any unexpected
 * errors that are not covered by the custom exception classes.
 */

require_once("ElevatorController.php");

// Create an elevator controller for testing.
$controller = new ElevatorController();

try
{
    // Test a valid CAN network node ID.
    $controller->validateNode(1);

    // Test a successful CAN network connection.
    $controller->checkConnection(true);

    // Test a valid sensor reading.
    $controller->readSensor(25);

    // Test an invalid floor request.
    // Floor 4 exceeds the controller's maximum floor of 3.
    $controller->requestFloor(4);
}
catch (InvalidFloorException $e)
{
    // Handle an invalid elevator floor request.
    echo "<b>INVALID FLOOR ERROR:</b> "
         . $e->getMessage();
}
catch (CommunicationException $e)
{
    // Handle a CAN network communication failure.
    echo "<b>COMMUNICATION ERROR:</b> "
         . $e->getMessage();
}
catch (SensorException $e)
{
    // Handle an invalid sensor reading.
    echo "<b>SENSOR ERROR:</b> "
         . $e->getMessage();
}
catch (NodeException $e)
{
    // Handle an invalid CAN node ID.
    echo "<b>NODE ERROR:</b> "
         . $e->getMessage();
}
catch (Exception $e)
{
    // Handle any unexpected exception.
    echo "<b>GENERAL ERROR:</b> "
         . $e->getMessage();
}

?>