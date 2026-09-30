<?php

/**
 * @file
 * @brief Tests the basic OOP classes used in the elevator system.
 *
 * Creates objects from the Node, ElevatorCar, FloorNode, CallButton,
 * and Sensor classes to demonstrate their basic functionality.
 *
 * The test displays node information, reads the elevator's current
 * floor, calls the elevator from a floor, presses a call button,
 * reads a sensor distance, and displays the total number of Node
 * objects created.
 */

require_once("Node.php");
require_once("ElevatorCar.php");
require_once("FloorNode.php");
require_once("CallButton.php");
require_once("Sensor.php");

// Create a Node object with elevator network information.
$node = new Node(
    1,
    "Active",
    2,
    3,
    "Normal Operation"
);

// Create an ElevatorCar object starting on floor 2 and moving up.
$elevator = new ElevatorCar(
    1,
    2,
    "Up"
);

// Create a FloorNode object representing floor 2.
$floorNode = new FloorNode(2);

// Create an upward CallButton object.
$button = new CallButton("Up");

// Create a Sensor object with an initial distance of 15.4.
$sensor = new Sensor(15.4);

// Display the test heading.
echo "<h2>Object Test</h2>";

// Display the node ID.
echo "Node ID: "
    . $node->getNodeID()
    . "<br>";

// Display the elevator's current floor.
echo "Current Floor: "
    . $elevator->getCurrentFloor()
    . "<br>";

// Test calling the elevator from the floor node.
$floorNode->callElevator();

// Test pressing the call button.
$button->pressButton();

// Display the current sensor distance.
echo "Sensor Distance: "
    . $sensor->readDistance()
    . "<br>";

// Display the total number of Node objects that have been created.
echo "Total Nodes Created: "
    . Node::getNodeCount();

?>
