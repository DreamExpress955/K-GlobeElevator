<?php

/**
 * @file
 * @brief Displays the current elevator floor using visual status indicators.
 *
 * Connects to the Elevator database using the functions provided by
 * databaseFunctions.php and retrieves the elevator's current floor.
 *
 * Three floor indicators are displayed on the page. The indicator for
 * the elevator's current floor is shown in green, while the indicators
 * for the other floors are shown in red.
 */

require '../php/databaseFunctions.php';

// Configure the Elevator database connection.
$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

// Database login credentials.
$user = 'phpmyadmin';
$password = 'ese1';

// Get the elevator's current floor from the database.
$curFlr = get_currentFloor(
    $path,
    $user,
    $password
);

?>

<!-- Container for the three elevator floor indicators. -->
<div class="d-flex justify-content-center gap-5">

<?php

/**
 * Create an indicator for each elevator floor.
 *
 * The elevator contains three floors. The current floor is displayed
 * using a green indicator and the remaining floors are displayed using
 * red indicators.
 */
for ($i = 1; $i <= 3; $i++):

?>

    <div>

        <!--
            Display the floor indicator.
            Green (#198754) represents the current floor.
            Red (#dc3545) represents an inactive floor.
        -->
        <div
            class="rounded-circle border border-dark mx-auto mb-2"
            style="
                width:80px;
                height:80px;
                background: <?= ($i == $curFlr) ? '#198754' : '#dc3545' ?>;
            ">
        </div>

        <!-- Display the floor number below the indicator. -->
        <strong>Floor <?= $i ?></strong>

    </div>

<?php endfor; ?>

</div>