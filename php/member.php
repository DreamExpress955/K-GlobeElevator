<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['username'])) {
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            You are not authorized! Please log in.
        </div>
    </div>
    ");
}
$currentMode = "Normal";

if (isset($_POST['mode'])) {

    if ($_POST['mode'] == 'sabbath') {
        $currentMode = "Sabbath";
    }

    if ($_POST['mode'] == 'maintenance') {
        $currentMode = "Maintenance";
    }
}

require '../php/databaseFunctions.php';

$host = '127.0.0.1';
$database = 'Elevator';
$tablename = 'elevatorNetwork';
$path = "mysql:host=$host;dbname=$database";
$user = 'root';
$password = '';

$db = connect($path, $user, $password);

$current_date = $db->query('SELECT CURRENT_DATE()')->fetchColumn();
$current_time = $db->query('SELECT CURRENT_TIME()')->fetchColumn();

$nodeID = $_POST['nodeID'] ?? '';
$status = $_POST['status'] ?? '';
$currentFloor = $_POST['currentFloor'] ?? '';
$requestedFloor = $_POST['requestedFloor'] ?? '';
$otherInfo = $_POST['otherInfo'] ?? '';

$message = "";

if (isset($_POST['insert'])) {
    insert($path, $user, $password, $nodeID, $current_date, $current_time, $status, $currentFloor, $requestedFloor, $otherInfo);
    $message = "<div class='alert alert-success'>Record inserted successfully.</div>";
}

if (isset($_POST['update'])) {
    update($path, $user, $password, $tablename, $nodeID, $status, $currentFloor, $requestedFloor, $otherInfo);
    $message = "<div class='alert alert-warning'>Record updated successfully.</div>";
}

if (isset($_POST['delete'])) {
    delete($path, $user, $password, $tablename, $nodeID);
    $message = "<div class='alert alert-danger'>Record deleted successfully.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Elevator Network Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.html">K-Globe</a>

        <div>
            <a href="../index.html" class="btn btn-outline-light me-2">
                Home
            </a>

            <a href="logout.php" class="btn btn-outline-danger">
                Logout
            </a>
        </div>
    </div>
</nav>

<div class="card shadow-lg mb-4">
    <div class="card-header bg-primary text-white">
        <h2 class="mb-0">Elevator Network Dashboard</h2>
    </div>

    <div class="card-body">

        <h4 class="mb-4">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
        </h4>

        <?= $message ?>

        <!-- FORM -->
        <?php require '../elevatorNetworkForm.html'; ?>

        <hr class="my-4">

        <!-- DATABASE RECORDS -->
<div class="card-header bg-primary text-white">
    <h3 class="mb-0">Database Records</h3>
</div>

<div class="table-responsive">
    <?php showtable($path, $user, $password, $tablename); ?>
</div>

</div>
</div>

<!-- ELEVATOR CONTROL PANEL -->
<div class="card shadow mt-4">
    <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Elevator Control Panel</h3>
    </div>

    <h5 class="mb-3">
    Current Mode:
    <span class="badge bg-success">
        <?= $currentMode ?>
    </span>
</h5>

    <div class="card-body">

        <form method="POST">

            <button type="submit"
                    name="mode"
                    value="normal"
                    class="btn btn-secondary">
                Normal Mode
            </button>

            <button type="submit"
                    name="mode"
                    value="sabbath"
                    class="btn btn-secondary">
                Sabbath Mode
            </button>

            <button type="submit"
                    name="mode"
                    value="maintenance"
                    class="btn btn-secondary">
                Maintenance Mode
            </button>

        </form>

    </div>
</div>
    <div class="card-body">

        <h5>Current Elevator Position</h5>

        <?php
        $curFlr = get_currentFloor($path, $user, $password);

echo "<div class='d-flex gap-3'>";

for ($i = 1; $i <= 3; $i++) {

    $color = ($i == $curFlr) ? "green" : "red";

    echo "
    <div style='
        width:50px;
        height:50px;
        border-radius:50%;
        background:$color;
        border:2px solid black;
    '></div>";
}

echo "</div>";

echo "</div>";

echo "</div>";

echo "</div>";

        echo "</div>";
        ?>

        <div class="row">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Request a Floor</div>

                    <div class="card-body">
                        <form method="POST">

                            <input type="hidden" name="request_type" value="floor_controller">

                            <button class="btn btn-primary w-100 mb-2" name="floor" value="1">
                                Floor 1
                            </button>

                            <button class="btn btn-primary w-100 mb-2" name="floor" value="2">
                                Floor 2
                            </button>

                            <button class="btn btn-primary w-100" name="floor" value="3">
                                Floor 3
                            </button>

                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Car Controller</div>

                    <div class="card-body">
                        <form method="POST">

                            <input type="hidden" name="request_type" value="car_controller">

                            <button class="btn btn-success w-100 mb-2" name="floor" value="1">
                                Floor 1
                            </button>

                            <button class="btn btn-success w-100 mb-2" name="floor" value="2">
                                Floor 2
                            </button>

                            <button class="btn btn-success w-100" name="floor" value="3">
                                Floor 3
                            </button>

                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Queue</div>

                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item">TEMP</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<div class="text-center mt-4">
    <a href="logout.php" class="btn btn-outline-danger">
        Sign Out
    </a>
</div>

</body>
</html>
