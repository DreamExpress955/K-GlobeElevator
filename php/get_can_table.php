<?php

/**
 * @file
 * @brief Displays the CAN message log from the elevator database.
 *
 * Loads the database functions used by the K-Globe Elevator project,
 * configures the connection to the Elevator MySQL database, and displays
 * the CAN message log using the showCANTable() function.
 */

require '../php/databaseFunctions.php';

// Configure the Elevator database connection.
$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

// Database login credentials.
$user = 'phpmyadmin';
$password = 'ese1';

// Display the CAN message log table.
showCANTable($path, $user, $password);

?>