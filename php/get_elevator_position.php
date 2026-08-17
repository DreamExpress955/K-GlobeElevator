<?php
require '../php/databaseFunctions.php';

$host = '127.0.0.1';
$database = 'Elevator';
$path = "mysql:host=$host;dbname=$database";

$user = 'phpmyadmin';
$password = 'ese1';

$curFlr = get_currentFloor($path, $user, $password);
?>

<div class="d-flex justify-content-center gap-5">

<?php for ($i = 1; $i <= 3; $i++): ?>

    <div>

        <div
            class="rounded-circle border border-dark mx-auto mb-2"
            style="
                width:80px;
                height:80px;
                background: <?= ($i == $curFlr) ? '#198754' : '#dc3545' ?>;
            ">
        </div>

        <strong>Floor <?= $i ?></strong>

    </div>

<?php endfor; ?>

</div>