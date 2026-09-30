<?php

/**
 * @file
 * @brief Tests inheritance and activation of elevator system components.
 *
 * Creates an ElevatorCar, multiple CallButton objects, and a
 * DistanceSensor to test the shared Node inheritance and component
 * functionality.
 *
 * The test moves the elevator, activates each call button, activates
 * the distance sensor, and displays the elevator's final floor.
 */

require_once("ElevatorCar.php");
require_once("CallButton.php");
require_once("DistanceSensor.php");

// Display the test heading.
echo "<h2>Inheritance Test</h2>";

// Create an elevator car starting on floor 1.
$elevator = new ElevatorCar(
    1,
    "Active",
    1
);

// Create an UP call button.
$buttonUp = new CallButton(
    2,
    "Active",
    "UP"
);

// Create a DOWN call button.
$buttonDown = new CallButton(
    3,
    "Active",
    "DOWN"
);

// Create an EMERGENCY call button.
$buttonEmergency = new CallButton(
    4,
    "Active",
    "EMERGENCY"
);

// Create a distance sensor with an initial reading of 25 cm.
$sensor = new DistanceSensor(
    5,
    "Active",
    25
);

// Test moving the elevator up one floor.
$elevator->moveUp();

// Test activation of each call button.
$buttonUp->activate();
$buttonDown->activate();
$buttonEmergency->activate();

// Test activation of the distance sensor.
$sensor->activate();

// Display the elevator's current floor after the movement test.
echo "<br>";
echo "Current Floor: "
    . $elevator->getCurrentFloor();

?>