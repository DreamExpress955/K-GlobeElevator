<?php

/**
 * @file
 * @brief Displays the current elevator door status.
 *
 * Connects to the Elevator database using the database functions
 * provided by databaseFunctions.php and retrieves the current door
 * status.
 *
 * A green Bootstrap badge is displayed when the elevator door is open,
 * and a red Bootstrap badge is displayed when the elevator door is closed.
 */

require '../php/databaseFunctions.php';

// Configure the Elevator database connection.
$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

// Database login credentials.
$user = 'phpmyadmin';
$password = 'ese1';

// Get the current elevator door status from the database.
$doorOpen = getDoorStatus(
    $path,
    $user,
    $password
);

// Display the elevator door status.
if ($doorOpen == 1)
{
    // Display a green badge when the elevator door is open.
    echo '
        <div class="badge bg-success fs-4">
            Door OPEN
        </div>
    ';
}
else
{
    // Display a red badge when the elevator door is closed.
    echo '
        <div class="badge bg-danger fs-4">
            Door CLOSED
        </div>
    ';
}

?>