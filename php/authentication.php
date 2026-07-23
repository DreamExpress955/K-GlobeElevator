<<?php
    // authenticate.php
    // start session
    session_start();

    // Get the submitted username and password from the POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new PDO('mysql:host=127.0.0.1;dbname=authorizedUsers', 'root', '');
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

     // Authenticate against the database
    $query = "SELECT * FROM authorizedUsers WHERE username = '$username'";
    $rows = $db->query($query);
    foreach ($rows as $row) {
        echo $row['username'];
        if($username === $row['username'] && $password === $row['password']) {
            $authenticated = TRUE;
        }
    }

    if($authenticated) {
        $_SESSION['username'] = $username;
        // Redirect immediately
        header("Location: member.php");
    } else {
        header("Location: req_access.php"); 
    }
?>