<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) 
{
    require 'databaseFunctions.php';
    // set up variables
    $host = '127.0.0.1';
    $user = 'root';
    $password = 'ese';
    $database = 'elevator';
    $table = 'elevatorNetwork';
    $path = "mysql:host=$host;dbname=$database";

    $db = connect($path, $user, $password);

    $current_date_query = $db->query('SELECT CURRENT_DATE()');
    $current_date = $current_date_query->fetch(PDO::FETCH_ASSOC);

    $current_time_query = $db->query('SELECT CURRENT_TIME()');
    $current_time = $current_time_query->fetch(PDO::FETCH_ASSOC);

    if(isset($_POST['nodeID'], $_POST['status'], $_POST['currentFloor'], $_POST['requestedFloor'], $_POST['OtherInfo']))
    {
        $nodeID = $_POST['nodeID'];
        $status = $_POST['status'];
        $currentFloor = $_POST['currentFloor'];
        $requestedFloor = $_POST['requestedFloor'];
        $OtherInfo = $_POST['OtherInfo'];

        $result = insertData($path, $user, $password, $current_date, $current_time, $nodeID, $status, $currentFloor, $requestedFloor, $OtherInfo);
    }

// Display welcome and form
    echo "<h1>Welcome, " . $_SESSION['username'] . "</h1>";
    require 'elevatorNetworkForm.html';

    if(isset($_POST['Insert'])) 
        {
            echo "You pressed INSERT <br>";
            insert($path, $user, $password, $current_date, $current_time, $status, $currentFloor, $requestedFloor, $otherInfo);
        }
    else if(isset($_POST['Update'])) 
        {
            echo "You pressed UPDATE <br>";
            update($path, $user, $password, $tablename, $nodeID, $status, $currentFloor, $requestedFloor, $otherInfo);
        }
    else if(isset($_POST['Delete'])) 
    {
    echo "You pressed DELETE <br>";
    delete($path, $user, $password, $tablename, $nodeID);
    }

// Display content of database
showtable($path, $user, $password, $tablename);

// Sign out option
echo "<p>Click <a href='logout.php'>here</a> to sign out</p>";
}
    else 
    {
        echo "<p>You are not authorized!!! Go away!!!!!</p>";
}

?>