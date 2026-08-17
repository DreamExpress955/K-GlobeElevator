<?php

require_once("ElevatorCar.php");
require_once("CallButton.php");
require_once("DistanceSensor.php");

echo "<h2>Inheritance Test</h2>";

$elevator = new ElevatorCar(
    1,
    "Active",
    1
);

$buttonUp = new CallButton(
    2,
    "Active",
    "UP"
);

$buttonDown = new CallButton(
    3,
    "Active",
    "DOWN"
);

$buttonEmergency = new CallButton(
    4,
    "Active",
    "EMERGENCY"
);

$sensor = new DistanceSensor(
    5,
    "Active",
    25
);

$elevator->moveUp();

$buttonUp->activate();
$buttonDown->activate();
$buttonEmergency->activate();

$sensor->activate();

echo "<br>";
echo "Current Floor: "
    . $elevator->getCurrentFloor();
?>