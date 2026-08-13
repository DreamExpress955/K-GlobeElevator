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
$tablename = 'CANLogs';
$path = "mysql:host=$host;dbname=$database";
$user = 'myphpadmin';
$password = 'ese1';

$db = connect($path, $user, $password);

$current_date = $db->query('SELECT CURRENT_DATE()')->fetchColumn();
$current_time = $db->query('SELECT CURRENT_TIME()')->fetchColumn();

$nodeID = $_POST['nodeID'] ?? '';
$status = $_POST['status'] ?? '';
$currentFloor = $_POST['currentFloor'] ?? '';
$requestedFloor = $_POST['requestedFloor'] ?? '';
$otherInfo = $_POST['otherInfo'] ?? '';
$canID = $_POST['canID'] ?? 0;
$canNodeID = $_POST['can_nodeID'] ?? 0;
$messageID = $_POST['messageID'] ?? 0;
$baudRate = $_POST['baudRate'] ?? 0;
$lastMessage = $_POST['lastMessage'] ?? '';

$message = "";

if (isset($_POST['insert'])) {
    insert(
    $path,
    $user,
    $password,
    $current_date,
    $current_time,
    (int)$status,
    (int)$currentFloor,
    (int)$requestedFloor,
    $otherInfo
);
    $message = "<div class='alert alert-success'>Record inserted successfully.</div>";
}
echo "<pre>";
print_r($_POST);
echo "</pre>";
if (isset($_POST['insertCAN'])) {

    insertCAN(
        $path,
        $user,
        $password,
        (int)$canNodeID,
        (int)$messageID,
        (int)$baudRate,
        $lastMessage
    );

    $message = "<div class='alert alert-success'>CAN record inserted successfully.</div>";
}

if (isset($_POST['updateCAN'])) {

    updateCAN(
        $path,
        $user,
        $password,
        (int)$canID,
        (int)$messageID,
        (int)$baudRate,
        $lastMessage
    );

    $message = "<div class='alert alert-warning'>CAN record updated successfully.</div>";
}

if (isset($_POST['deleteCAN'])) {

    deleteCAN(
        $path,
        $user,
        $password,
        (int)$canID
    );

    $message = "<div class='alert alert-danger'>CAN record deleted successfully.</div>";
}

if (isset($_POST['update'])) {
    update(
        $path,
        $user,
        $password,
        (int)$nodeID,
        (int)$status,
        (int)$currentFloor,
        (int)$requestedFloor,
        $otherInfo
    );
    $message = "<div class='alert alert-warning'>Record updated successfully.</div>";
}


if (isset($_POST['delete'])) {
    delete(
        $path,
        $user,
        $password,
        (int)$nodeID
    );
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

<!-- ELEVATOR FORM -->
<div class="card mb-4">

    <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Elevator Network Controls</h3>
    </div>

    <div class="card-body">
        <?php require '../elevatorNetworkForm.html'; ?>
    </div>

</div>

<!-- CAN FORM -->
<div class="card mb-4">

    <div class="card-header bg-success text-white">
        <h3 class="mb-0">CAN Network Controls</h3>
    </div>

    <div class="card-body">

        <form method="POST">

            <input type="number"
                   name="canID"
                   class="form-control mb-2"
                   placeholder="CAN ID">

            <input type="number"
                   name="can_nodeID"
                   class="form-control mb-2"
                   placeholder="Node ID">

            <input type="number"
                   name="messageID"
                   class="form-control mb-2"
                   placeholder="Message ID">

            <input type="number"
                   name="baudRate"
                   class="form-control mb-2"
                   placeholder="Baud Rate">

            <input type="text"
                   name="lastMessage"
                   class="form-control mb-3"
                   placeholder="Last Message">

            <button type="submit"
                    name="insertCAN"
                    class="btn btn-success">
                Insert CAN
            </button>

            <button type="submit"
                    name="updateCAN"
                    class="btn btn-warning">
                Update CAN
            </button>

            <button type="submit"
                    name="deleteCAN"
                    class="btn btn-danger">
                Delete CAN
            </button>

        </form>

    </div>

</div>
        <hr class="my-4">

        <!-- DATABASE RECORDS -->
<div class="card-header bg-primary text-white">
    <h3 class="mb-0">Database Records</h3>
</div>

<div class="table-responsive">
    
    <?php showCANtable($path, $user, $password); ?>
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
