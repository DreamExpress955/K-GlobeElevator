<?php
//update elevator network current floor
function update_elevatorNetwork(int $node_ID, int $new_floor =1, int $requestType): int {
		$db = get_database();

    $query = '
        UPDATE elevatorNetwork
        SET requestedType = :floorT,
            requestedFloor = :floor
        WHERE nodeID = :id
    ';

    $statement = $db->prepare($query);
    $statement->bindValue(':floor', $new_floor, PDO::PARAM_INT);
    $statement->bindValue(':id', $node_ID, PDO::PARAM_INT);
    $statement->bindValue(':floorT', $requestType, PDO::PARAM_INT);
    $statement->execute();

    return $new_floor;
		
	}
function get_database(): PDO
{
    return new PDO(
        'mysql:host=127.0.0.1;dbname=Elevator;charset=utf8mb4',
        'myphpadmin',
        'ese1',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}


error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../php/databaseFunctions.php';
session_start();

if (!isset($_SESSION['mode'])) {
    $_SESSION['mode'] = 'Normal';
}

if (!isset($_SESSION['username'])) {
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            You are not authorized! Please log in.
        </div>
    </div>
    ");
}
$host = '127.0.0.1';
$database = 'Elevator';
$tablename = 'CANLogs';
$path = "mysql:host=$host;dbname=$database";
//Blakes PiConnect database connection
$user = 'myphpadmin';
$password = 'ese1';
//Oen's local database connection
//$user = 'root';
//$password = '';
$currentMode = "Normal";

$db = connect($path, $user, $password);

$logCount = getLogCount($path, $user, $password);

$maintenanceState = getMaintenanceStatus($logCount);

// Handle mode button clicks
if (isset($_POST['mode'])) {

    switch ($_POST['mode']) {

        case 'normal':

            $_SESSION['mode'] = 'Normal';

            setMode(
                $path,
                $user,
                $password,
                0
            );

            break;

        case 'sabbath':

            $_SESSION['mode'] = 'Sabbath';

            setMode(
                $path,
                $user,
                $password,
                1
            );

            break;

        case 'maintenance':

            $_SESSION['mode'] = 'Maintenance';

            setMode(
                $path,
                $user,
                $password,
                2
            );

            break;
    }
}

// Automatic maintenance override
if ($maintenanceState['maintenance']) {

    $_SESSION['mode'] = 'Maintenance';

    setMode(
        $path,
        $user,
        $password,
        2
    );
}
$currentMode = $_SESSION['mode'];

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['floor'])) {
    try {
        $requestType = $_POST['request_type'] ?? '';
        $requestedFloor = filter_input(
            INPUT_POST,
            'floor',
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 3
                ]
            ]
        );

        if ($requestedFloor === false || $requestedFloor === null) {
            throw new InvalidArgumentException('The selected floor is invalid.');
        }
        $request = 0;
        $nodeID = 1;
        if ($requestType === 'floor_controller') {
            /*
             * Floor-controller requests:
             */
            $request = 0;
        } elseif ($requestType === 'car_controller') {
            /*
             * All three car buttons update the car-controller node.
             */
            $request = 1;
        } else {
            throw new InvalidArgumentException('The request type is invalid.');
        }

        update_elevatorNetwork($nodeID, $requestedFloor, $request);

        // Prevent duplicate form submission when the page is refreshed.
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Throwable $error) {
        $errorMessage = $error->getMessage();
    }
}

try {
    $curFlr = get_currentFloor();
} catch (Throwable $error) {
    $curFlr = 1;
    $errorMessage = $error->getMessage();
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

    <div class="card-body">
        <?php require '../elevatorNetworkForm.html'; ?>
    </div>

</div>

        <!-- DATABASE RECORDS -->
<div class="card-header bg-primary text-white">
    <h3 class="mb-0">Database Records</h3>
</div>

<div id="canTableContainer" class="table-responsive">
    <?php showCANtable($path, $user, $password); ?>
</div>

</div>
</div>



<!-- ELEVATOR CONTROL PANEL -->
<div class="container-fluid mt-4">

    <div class="card shadow border-0">

        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Elevator Control Panel</h3>
        </div>

        <div class="card-body">

            <!-- STATUS -->
            <div class="row mb-4">

                <div class="col-md-6">

                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">

                            <h6 class="text-muted">
                                Database Records
                            </h6>

                            <div id="logCount">
                            <h2 class="fw-bold">
                            <?= $logCount ?>
                            </h2>


                            </div>

                        </div>
                    </div>

                </div>

                <div class="col-md-6">

                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">

                            <h6 class="text-muted">
                                Current Mode
                            </h6>

                            <span class="badge fs-5 px-3 py-2
                            <?php
                            switch ($currentMode) {

                                case 'Maintenance':
                                    echo 'bg-danger';
                                    break;

                                case 'Sabbath':
                                    echo 'bg-secondary';
                                    break;

                                default:
                                    echo 'bg-success';
                            }
                            ?>">
                                <?= htmlspecialchars($currentMode) ?>
                            </span>

                        </div>
                    </div>

                </div>

            </div>

            <!-- MODE BUTTONS -->
            <div class="card mb-4">

                <div class="card-header bg-dark text-white">
                    Mode Controls
                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="row g-2">

                            <div class="col-md-4">
                                <button
                                    type="submit"
                                    name="mode"
                                    value="normal"
                                    class="btn btn-success w-100">
                                    Normal Mode
                                </button>
                            </div>

                            <div class="col-md-4">
                                <button
                                    type="submit"
                                    name="mode"
                                    value="sabbath"
                                    class="btn btn-secondary w-100">
                                    Sabbath Mode
                                </button>
                            </div>

                            <div class="col-md-4">
                                <button
                                    type="submit"
                                    name="mode"
                                    value="maintenance"
                                    class="btn btn-danger w-100">
                                    Maintenance Mode
                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

<!-- ELEVATOR POSITION -->
<div class="card mb-4">

    <div class="card-header bg-info text-white">
        Elevator Status
    </div>

    <div class="card-body">

        <div class="row">

            <!-- FLOOR POSITION -->
            <div class="col-md-8">

                <h5 class="text-center mb-3">
                    Elevator Position
                </h5>

                <div id="elevatorPosition">

                    <?php $curFlr = get_currentFloor($path, $user, $password); ?>

                    <div class="d-flex justify-content-center gap-5">

                        <?php for ($i = 1; $i <= 3; $i++): ?>

                            <div>

                                <div
                                    class="rounded-circle border border-dark mx-auto mb-2"
                                    style="
                                        width:80px;
                                        height:80px;
                                        background:
                                        <?= ($i == $curFlr)
                                            ? '#198754'
                                            : '#dc3545' ?>;
                                    ">
                                </div>

                                <strong>Floor <?= $i ?></strong>

                            </div>

                        <?php endfor; ?>

                    </div>

                </div>

            </div>

            <!-- DOOR STATUS -->
            <div class="col-md-4 text-center">

                <h5 class="mb-3">
                    Door Status
                </h5>

                <div id="doorStatus">
                    Loading...
                </div>

            </div>

        </div>

    </div>

</div>

            <!-- FLOOR CONTROLS -->
            <div class="row">

                <!-- FLOOR CONTROLLER -->
                <div class="col-lg-6 mb-3">

                    <div class="card h-100">

                        <div class="card-header bg-primary text-white">
                            Floor Controller
                        </div>

                        <div class="card-body">

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="request_type"
                                    value="floor_controller">

                                <button
                                    class="btn btn-primary w-100 mb-2"
                                    name="floor"
                                    value="1">
                                    Floor 1
                                </button>

                                <button
                                    class="btn btn-primary w-100 mb-2"
                                    name="floor"
                                    value="2">
                                    Floor 2
                                </button>

                                <button
                                    class="btn btn-primary w-100"
                                    name="floor"
                                    value="3">
                                    Floor 3
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

                <!-- CAR CONTROLLER -->
                <div class="col-lg-6 mb-3">

                    <div class="card h-100">

                        <div class="card-header bg-success text-white">
                            Car Controller
                        </div>

                        <div class="card-body">

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="request_type"
                                    value="car_controller">

                                <button
                                    class="btn btn-success w-100 mb-2"
                                    name="floor"
                                    value="1">
                                    Floor 1
                                </button>

                                <button
                                    class="btn btn-success w-100 mb-2"
                                    name="floor"
                                    value="2">
                                    Floor 2
                                </button>

                                <button
                                    class="btn btn-success w-100"
                                    name="floor"
                                    value="3">
                                    Floor 3
                                </button>

                            </form>

                        </div>

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

<script>
function refreshCANTable() {
    fetch('get_can_table.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('canTableContainer').innerHTML = html;
        })
        .catch(error => console.error('Refresh failed:', error));
}

// Initial load
refreshCANTable();

// Refresh every 2 seconds
setInterval(refreshCANTable, 2000);
</script>

<script>
function refreshElevatorPosition() {

    console.log("Polling elevator position...");

    fetch('get_elevator_position.php')  // Append timestamp to avoid caching
        .then(response => response.text())
        .then(data => {
            console.log("Received:", data);

            document.getElementById('elevatorPosition').innerHTML = data;
        })
        .catch(error => {
            console.error(error);
        });
}

refreshElevatorPosition();

setInterval(refreshElevatorPosition, 1000);
</script>

<script>
function refreshDoorStatus() {

    fetch('get_door_status.php?t=' + Date.now())
        .then(response => response.text())
        .then(data => {
            document.getElementById('doorStatus').innerHTML = data;
        })
        .catch(error => console.error(error));
}

refreshDoorStatus();

setInterval(refreshDoorStatus, 1000);
</script>


<script>
function refreshLogCount() {

    fetch('get_log_count.php?t=' + Date.now())
        .then(response => response.text())
        .then(data => {
            document.getElementById('logCount').innerHTML = data;
        })
        .catch(error => {
            console.error(error);
        });
}

refreshLogCount();

setInterval(refreshLogCount, 1000);
</script>




</body>
</html>
