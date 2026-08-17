<?php
require '../php/databaseFunctions.php';

$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

$user = 'phpmyadmin';
$password = 'ese1';

showCANtable($path, $user, $password);
?>
