<?php

/**
 * @file
 * @brief Displays the total number of CAN log records.
 *
 * Connects to the Elevator database using the functions provided by
 * databaseFunctions.php and retrieves the total number of records
 * stored in the CANLogs table.
 *
 * The resulting log count is displayed on the webpage as a bold
 * heading.
 */

require '../php/databaseFunctions.php';

// Configure the Elevator database connection.
$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

// Database login credentials.
$user = 'phpmyadmin';
$password = 'ese1';

// Get the total number of records stored in the CANLogs table.
$logCount = getLogCount(
    $path,
    $user,
    $password
);

// Display the total CAN log count.
echo "<h2 class='fw-bold'>{$logCount}</h2>";

?>