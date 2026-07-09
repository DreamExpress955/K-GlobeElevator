<<?php
    session_start();

    // Hardcoded valid credentials
    $validUser = "Admin";
    $validPassword = "Password";

    // Get the submitted username and password from the POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the submitted credentials match the valid credentials
    if ($username === $validUser && $password === $validPassword) 
    {
        // If the credentials are valid, set a session variable to indicate the user is logged in
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // Redirect to a protected page 
        header("Location: member.php");
        exit();
    } 
    else 
    {
        // If the credentials are invalid, redirect back to the login page with an error message
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: ../RequestAccessPage.html");
        exit();
    }
?>