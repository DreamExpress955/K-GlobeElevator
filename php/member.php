<?php

/**
 * @file
 * @brief Provides the main K-Globe Elevator Network Dashboard.
 *
 * This page provides an authenticated dashboard for monitoring and
 * controlling the K-Globe elevator system.
 *
 * The dashboard provides database record management, CAN network
 * information, elevator position monitoring, door status monitoring,
 * operating mode control, maintenance monitoring, and manual floor
 * requests from the floor and car controllers.
 *
 * Several dashboard components are refreshed automatically using
 * JavaScript fetch requests without requiring the entire page to reload.
 */


/**
 * @brief Updates an elevator network floor request.
 *
 * Updates the requested floor and request type for a specified node
 * in the elevatorNetwork table.
 *
 * The request type identifies whether the request originated from the
 * floor controller or the elevator car controller.
 *
 * @param int $node_ID ID of the elevator network node to update.
 * @param int $new_floor Requested elevator floor.
 * @param int $requestType Type of floor request.
 *
 * @return int The requested floor that was stored in the database.
 */
function update_elevatorNetwork(
    int $node_ID,
    int $new_floor,
    int $requestType
): int
{
    $db = get_database();

    $query = '
        UPDATE elevatorNetwork
        SET requestedType = :floorT,
            requestedFloor = :floor
        WHERE nodeID = :id
    ';

    $statement = $db->prepare($query);

    $statement->bindValue(
        ':floor',
        $new_floor,
        PDO::PARAM_INT
    );

    $statement->bindValue(
        ':id',
        $node_ID,
        PDO::PARAM_INT
    );

    $statement->bindValue(
        ':floorT',
        $requestType,
        PDO::PARAM_INT
    );

    $statement->execute();

    return $new_floor;
}


/**
 * @brief Creates a connection to the Elevator database.
 *
 * Creates a PDO connection to the Elevator MySQL database. PDO is
 * configured to throw exceptions when database operations fail and
 * return query results as associative arrays.
 *
 * @return PDO Connection to the Elevator database.
 */
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


// Enable PHP error reporting during development.
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Load the shared elevator database functions.
require '../php/databaseFunctions.php';


// Start or resume the current user session.
session_start();


// Initialize the operating mode when no mode has been stored.
if (!isset($_SESSION['mode']))
{
    $_SESSION['mode'] = 'Normal';
}


// Prevent unauthenticated users from accessing the dashboard.
if (!isset($_SESSION['username']))
{
    die("
    <div class='container mt-5'>
        <div class='alert alert-danger'>
            You are not authorized! Please log in.
        </div>
    </div>
    ");
}


// Configure the Elevator database connection.
$host = '127.0.0.1';
$database = 'Elevator';
$tablename = 'CANLogs';

$path = "mysql:host=$host;dbname=$database";


// Blake's PiConnect database connection.
$user = 'myphpadmin';
$password = 'ese1';


// Owen's local database connection.
// $user = 'root';
// $password = '';


// Default elevator operating mode.
$currentMode = "Normal";


// Connect to the elevator database.
$db = connect(
    $path,
    $user,
    $password
);


// Get the current number of CAN log records.
$logCount = getLogCount(
    $path,
    $user,
    $password
);


// Determine the current maintenance state.
$maintenanceState = getMaintenanceStatus(
    $logCount
);


// Handle operating mode button requests.
if (isset($_POST['mode']))
{
    switch ($_POST['mode'])
    {
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


// Automatically place the elevator into maintenance mode when
// the maintenance threshold has been reached.
if ($maintenanceState['maintenance'])
{
    $_SESSION['mode'] = 'Maintenance';

    setMode(
        $path,
        $user,
        $password,
        2
    );
}


// Store the current operating mode for display on the dashboard.
$currentMode = $_SESSION['mode'];


// Get the current database date and time.
$current_date = $db
    ->query('SELECT CURRENT_DATE()')
    ->fetchColumn();

$current_time = $db
    ->query('SELECT CURRENT_TIME()')
    ->fetchColumn();


// Retrieve elevator database form values.
$nodeID = $_POST['nodeID'] ?? '';
$status = $_POST['status'] ?? '';
$currentFloor = $_POST['currentFloor'] ?? '';
$requestedFloor = $_POST['requestedFloor'] ?? '';
$otherInfo = $_POST['otherInfo'] ?? '';


// Retrieve CAN database form values.
$canID = $_POST['canID'] ?? 0;
$canNodeID = $_POST['can_nodeID'] ?? 0;
$messageID = $_POST['messageID'] ?? 0;
$baudRate = $_POST['baudRate'] ?? 0;
$lastMessage = $_POST['lastMessage'] ?? '';


// Message displayed after a database operation.
$message = "";


// Insert a new elevator record.
if (isset($_POST['insert']))
{
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

    $message =
        "<div class='alert alert-success'>Record inserted successfully.</div>";
}


// Display POST data for debugging.
echo "<pre>";
print_r($_POST);
echo "</pre>";


// Insert a CAN network record.
if (isset($_POST['insertCAN']))
{
    insertCAN(
        $path,
        $user,
        $password,
        (int)$canNodeID,
        (int)$messageID,
        (int)$baudRate,
        $lastMessage
    );

    $message =
        "<div class='alert alert-success'>CAN record inserted successfully.</div>";
}


// Update a CAN network record.
if (isset($_POST['updateCAN']))
{
    updateCAN(
        $path,
        $user,
        $password,
        (int)$canID,
        (int)$messageID,
        (int)$baudRate,
        $lastMessage
    );

    $message =
        "<div class='alert alert-warning'>CAN record updated successfully.</div>";
}


// Delete a CAN network record.
if (isset($_POST['deleteCAN']))
{
    deleteCAN(
        $path,
        $user,
        $password,
        (int)$canID
    );

    $message =
        "<div class='alert alert-danger'>CAN record deleted successfully.</div>";
}


// Update an elevator network record.
if (isset($_POST['update']))
{
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

    $message =
        "<div class='alert alert-warning'>Record updated successfully.</div>";
}


// Delete an elevator network record.
if (isset($_POST['delete']))
{
    delete(
        $path,
        $user,
        $password,
        (int)$nodeID
    );

    $message =
        "<div class='alert alert-danger'>Record deleted successfully.</div>";
}


// Process floor requests from either the floor controller
// or the elevator car controller.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['floor'])
)
{
    try
    {
        // Determine which controller submitted the request.
        $requestType = $_POST['request_type'] ?? '';

        // Validate that the requested floor is between 1 and 3.
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

        if (
            $requestedFloor === false ||
            $requestedFloor === null
        )
        {
            throw new InvalidArgumentException(
                'The selected floor is invalid.'
            );
        }

        // Request type:
        // 0 = Floor Controller
        // 1 = Car Controller
        $request = 0;

        // Floor requests currently update elevator network node 1.
        $nodeID = 1;


        if ($requestType === 'floor_controller')
        {
            // Request originated from the floor controller.
            $request = 0;
        }
        elseif ($requestType === 'car_controller')
        {
            // Request originated from the car controller.
            $request = 1;
        }
        else
        {
            throw new InvalidArgumentException(
                'The request type is invalid.'
            );
        }


        // Store the requested floor and request type in the database.
        update_elevatorNetwork(
            $nodeID,
            $requestedFloor,
            $request
        );


        // Prevent duplicate form submission when the page is refreshed.
        header(
            'Location: ' . $_SERVER['PHP_SELF']
        );

        exit;
    }
    catch (Throwable $error)
    {
        $errorMessage = $error->getMessage();
    }
}


// Attempt to retrieve the current elevator floor.
try
{
    $curFlr = get_currentFloor(
        $path,
        $user,
        $password
    );
}
catch (Throwable $error)
{
    // Default to floor 1 if the current floor cannot be retrieved.
    $curFlr = 1;

    $errorMessage = $error->getMessage();
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Elevator Network Dashboard</title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet">

</head>


<body class="bg-light">


<!-- =========================================================
     NAVIGATION BAR
     ========================================================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="index.html">
            K-Globe
        </a>


        <div>

            <a
                href="index.html">
                Home
            </a>

            <a
                href="logout.php"
                class="btn btn-outline-danger">
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================================================
     MAIN DASHBOARD
     ========================================================= -->

<div class="card shadow-lg mb-4">

    <div class="card-header bg-primary text-white">

        <h2 class="mb-0">
            Elevator Network Dashboard
        </h2>

    </div>


    <div class="card-body">

        <h4 class="mb-4">
            Welcome,
            <?= htmlspecialchars($_SESSION['username']) ?>
        </h4>


        <!-- Display database operation messages. -->
        <?= $message ?>


        <!-- =================================================
             ELEVATOR DATABASE FORM
             ================================================= -->

        <div class="card mb-4">

            <div class="card-body">

                <?php
                require '../elevatorNetworkForm.html';
                ?>

            </div>

        </div>


        <!-- =================================================
             DATABASE RECORDS
             ================================================= -->

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">
                Database Records
            </h3>

        </div>


        <!--
            CAN table contents are automatically refreshed
            by JavaScript later in this file.
        -->
        <div
            id="canTableContainer"
            class="table-responsive">

            <?php
            showCANTable(
                $path,
                $user,
                $password
            );
            ?>

        </div>

    </div>

</div>


<!-- =========================================================
     ELEVATOR CONTROL PANEL
     ========================================================= -->

<div class="container-fluid mt-4">

    <div class="card shadow border-0">

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">
                Elevator Control Panel
            </h3>

        </div>


        <div class="card-body">


            <!-- =============================================
                 DASHBOARD STATUS
                 ============================================= -->

            <div class="row mb-4">


                <!-- DATABASE RECORD COUNT -->

                <div class="col-md-6">

                    <div class="card border-0 bg-light">

                        <div class="card-body text-center">

                            <h6 class="text-muted">
                                Database Records
                            </h6>


                            <!--
                                This value is automatically refreshed
                                using get_log_count.php.
                            -->
                            <div id="logCount">

                                <h2 class="fw-bold">
                                    <?= $logCount ?>
                                </h2>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- CURRENT OPERATING MODE -->

                <div class="col-md-6">

                    <div class="card border-0 bg-light">

                        <div class="card-body text-center">

                            <h6 class="text-muted">
                                Current Mode
                            </h6>


                            <!--
                                Badge colour changes according to the
                                selected elevator operating mode.
                            -->
                            <span
                                class="badge fs-5 px-3 py-2
                                <?php

                                switch ($currentMode)
                                {
                                    case 'Maintenance':
                                        echo 'bg-danger';
                                        break;

                                    case 'Sabbath':
                                        echo 'bg-secondary';
                                        break;

                                    default:
                                        echo 'bg-success';
                                }

                                ?>"
                            >

                                <?= htmlspecialchars($currentMode) ?>

                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =============================================
                 MODE CONTROLS
                 ============================================= -->

            <div class="card mb-4">

                <div class="card-header bg-dark text-white">
                    Mode Controls
                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="row g-2">

                            <!-- Normal operating mode. -->
                            <div class="col-md-4">

                                <button
                                    type="submit"
                                    name="mode"
                                    value="normal"
                                    class="btn btn-success w-100">
                                    Normal Mode
                                </button>

                            </div>

                            <!-- Sabbath operating mode. -->
                            <div class="col-md-4">

                                <button
                                    type="submit"
                                    name="mode"
                                    value="sabbath"
                                    class="btn btn-secondary w-100">
                                    Sabbath Mode
                                </button>

                            </div>

                            <!-- Maintenance operating mode. -->
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


            <!-- =============================================
                 ELEVATOR POSITION AND DOOR STATUS
                 ============================================= -->

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

                            <!--
                                This section initially displays the elevator
                                position from the database. JavaScript then
                                refreshes the contents automatically.
                            -->
                            <div id="elevatorPosition">

                                <?php
                                $curFlr = get_currentFloor(
                                    $path,
                                    $user,
                                    $password
                                );
                                ?>

                                <div class="d-flex justify-content-center gap-5">

                                    <?php for ($i = 1; $i <= 3; $i++): ?>

                                        <div>

                                            <!--
                                                Green indicates the current
                                                elevator floor. Red indicates
                                                an inactive floor.
                                            -->
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

                                            <strong>
                                                Floor <?= $i ?>
                                            </strong>

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

                            <!--
                                Door status is automatically loaded from
                                get_door_status.php using JavaScript.
                            -->
                            <div id="doorStatus">
                                Loading...
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =============================================
                 FLOOR CONTROLS
                 ============================================= -->

            <div class="row">

                <!-- FLOOR CONTROLLER -->
                <div class="col-lg-6 mb-3">

                    <div class="card h-100">

                        <div class="card-header bg-primary text-white">
                            Floor Controller
                        </div>

                        <div class="card-body">

                            <form method="POST">

                                <!--
                                    Identifies the request as originating
                                    from the floor controller.
                                -->
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

                                <!--
                                    Identifies the request as originating
                                    from the elevator car controller.
                                -->
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


<!-- =============================================
     SIGN OUT
     ============================================= -->

<div class="text-center mt-4">

    <a
        href="logout.php"
        class="btn btn-outline-danger">
        Sign Out
    </a>

</div>


<!-- =============================================
     CAN TABLE AUTO REFRESH
     ============================================= -->

<script>

/**
 * @brief Refreshes the CAN database table.
 *
 * Requests the latest CAN table from get_can_table.php and replaces
 * the contents of the canTableContainer element.
 *
 * @return {void}
 */
function refreshCANTable()
{
    fetch('get_can_table.php')
        .then(response => response.text())
        .then(html =>
        {
            document.getElementById(
                'canTableContainer'
            ).innerHTML = html;
        })
        .catch(error =>
        {
            console.error(
                'Refresh failed:',
                error
            );
        });
}

// Load the CAN table immediately.
refreshCANTable();

// Refresh the CAN table every 2 seconds.
setInterval(
    refreshCANTable,
    2000
);

</script>


<!-- =============================================
     ELEVATOR POSITION AUTO REFRESH
     ============================================= -->

<script>

/**
 * @brief Refreshes the elevator position display.
 *
 * Requests the latest elevator position display from
 * get_elevator_position.php and places the returned HTML inside
 * the elevatorPosition element.
 *
 * @return {void}
 */
function refreshElevatorPosition()
{
    console.log(
        "Polling elevator position..."
    );

    fetch('get_elevator_position.php')
        .then(response => response.text())
        .then(data =>
        {
            console.log(
                "Received:",
                data
            );

            document.getElementById(
                'elevatorPosition'
            ).innerHTML = data;
        })
        .catch(error =>
        {
            console.error(error);
        });
}

// Load the elevator position immediately.
refreshElevatorPosition();

// Refresh the elevator position every second.
setInterval(
    refreshElevatorPosition,
    1000
);

</script>


<!-- =============================================
     DOOR STATUS AUTO REFRESH
     ============================================= -->

<script>

/**
 * @brief Refreshes the elevator door status.
 *
 * Requests the current door status from get_door_status.php and
 * displays the returned HTML inside the doorStatus element.
 *
 * A timestamp is added to the request to prevent the browser from
 * using a cached response.
 *
 * @return {void}
 */
function refreshDoorStatus()
{
    fetch(
        'get_door_status.php?t=' + Date.now()
    )
        .then(response => response.text())
        .then(data =>
        {
            document.getElementById(
                'doorStatus'
            ).innerHTML = data;
        })
        .catch(error =>
        {
            console.error(error);
        });
}

// Load the door status immediately.
refreshDoorStatus();

// Refresh the door status every second.
setInterval(
    refreshDoorStatus,
    1000
);

</script>


<!-- =============================================
     DATABASE LOG COUNT AUTO REFRESH
     ============================================= -->

<script>

/**
 * @brief Refreshes the number of database log records.
 *
 * Requests the latest CAN log count from get_log_count.php and
 * replaces the contents of the logCount element.
 *
 * A timestamp is included in the request to prevent a cached
 * response from being returned.
 *
 * @return {void}
 */
function refreshLogCount()
{
    fetch(
        'get_log_count.php?t=' + Date.now()
    )
        .then(response => response.text())
        .then(data =>
        {
            document.getElementById(
                'logCount'
            ).innerHTML = data;
        })
        .catch(error =>
        {
            console.error(error);
        });
}

// Load the log count immediately.
refreshLogCount();

// Refresh the database log count every second.
setInterval(
    refreshLogCount,
    1000
);

</script>

</body>

</html>