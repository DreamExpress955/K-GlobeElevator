<?php

require '../php/databaseFunctions.php';

$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

$user = 'root';
$password = '';

$doorOpen = getDoorStatus(
    $path,
    $user,
    $password
);

if ($doorOpen == 1) {

    echo '
        <div class="badge bg-success fs-4">
            Door OPEN
        </div>
    ';

} else {

    echo '
        <div class="badge bg-danger fs-4">
            Door CLOSED
        </div>
    ';
}
