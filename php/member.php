<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

    // member.php
    session_start(); 

    // Members only section
    if(isset($_SESSION['username'])) {
        // Include the database functions file
        require '../php/databaseFunctions.php';

        // Initialize variables
        $host = '127.0.0.1'; 
        $database = 'Elevator'; 
        $tablename = 'elevatorNetwork'; 
        $path = 'mysql:host=' . $host . ';dbname=' . $database; 
        $user = 'root'; 
        $password = '';

        // Connect to database and make changes
        $db = connect($path, $user, $password);
        
        // Get data from db and/or form       
        $current_date = $db->query('SELECT CURRENT_DATE()')->fetchColumn();
        $current_time = $db->query('SELECT CURRENT_TIME()')->fetchColumn();
        $nodeID = '';
        $status = '';
        $currentFloor = '';
        $requestedFloor = '';
        $otherInfo = '';

        if(isset($_POST['nodeID'])) { $nodeID = $_POST['nodeID']; }
        if(isset($_POST['status'])) { $status = $_POST['status']; }
        if(isset($_POST['currentFloor'])) { $currentFloor = $_POST['currentFloor']; }
        if(isset($_POST['requestedFloor'])) { $requestedFloor = $_POST['requestedFloor']; }
        if(isset($_POST['otherInfo'])) { $otherInfo = $_POST['otherInfo']; }

        // Display welcome and form
        echo "<h1>Welcome, " . $_SESSION['username'] . "</h1>";
        require '../elevatorNetworkForm.html';
            
        if(isset($_POST['insert'])) {
            echo "You pressed INSERT <br>"; 
            insert($path, $user, $password, $nodeID, $current_date, $current_time, $status, $currentFloor, $requestedFloor, $otherInfo);

        } elseif(isset($_POST['update'])) {
            echo "You pressed UPDATE <br>";
            update($path, $user, $password, $tablename, $nodeID, $status, $currentFloor, $requestedFloor, $otherInfo);

        } elseif(isset($_POST['delete'])) {
            echo 'You pressed DELETE <br>';
            delete($path, $user, $password, $tablename, $nodeID);
        } 
        // Display content of database
        showtable($path, $user, $password, $tablename);
        // Sign out option
        echo "<p>Click <a href='logout.php'>here</a> to sign out</p>";
    } else {
        echo "<p>You are not authorized!!! Go away!!!!!</p>";
    }

?>
