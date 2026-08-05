<?php

require_once("Node.php");
require_once("ElevatorCar.php");
require_once("FloorNode.php");
require_once("CallButton.php");
require_once("Sensor.php");

$node = new Node(
    1,
    "Active",
    2,
    3,
    "Normal Operation"
);

$elevator = new ElevatorCar(
    1,
    2,
    "Up"
);

$floorNode = new FloorNode(2);

$button = new CallButton("Up");

$sensor = new Sensor(15.4);

echo "<h2>Object Test</h2>";

echo "Node ID: "
    . $node->getNodeID()
    . "<br>";

echo "Current Floor: "
    . $elevator->getCurrentFloor()
    . "<br>";

$floorNode->callElevator();

$button->pressButton();

echo "Sensor Distance: "
    . $sensor->readDistance()
    . "<br>";

echo "Total Nodes Created: "
    . Node::getNodeCount();
?>