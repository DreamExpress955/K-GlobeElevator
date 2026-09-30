<?php

/**
 * @file
 * @brief Provides the access request page for the K-Globe Elevator system.
 *
 * Allows a user to request access to the elevator dashboard by submitting
 * a username and password.
 *
 * Submitted credentials are inserted into the authorizedUsers database.
 * After a successful request, the user is redirected to the login page.
 */

// Enable PHP error reporting during development.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the shared database functions.
require '../php/databaseFunctions.php';


// Configure the authorizedUsers database connection.
$host = '127.0.0.1';
$database = 'authorizedUsers';

$user = 'myphpadmin';
$password = 'ese1';

$path = "mysql:host=$host;dbname=$database";


// Connect to the authorizedUsers database.
$db = connect(
    $path,
    $user,
    $password
);


// Message displayed to the user if the request cannot be processed.
$message = "";


// Process the access request form.
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Remove extra whitespace from the submitted values.
    $username = trim($_POST['username']);
    $userPassword = trim($_POST['password']);


    // Make sure both required fields have been completed.
    if (!empty($username) && !empty($userPassword))
    {
        /*
         * Insert the requested username and password into the
         * authorizedUsers table.
         *
         * A prepared statement is used to pass the submitted
         * values to the database.
         */
        $sql = "
            INSERT INTO authorizedUsers
            (username, password)
            VALUES
            (:username, :password)
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':password' => $userPassword
        ]);


        // Redirect the user to the login page after submission.
        header("Location: ../login.html");
        exit();
    }
    else
    {
        // Display an error when one or more fields are empty.
        $message = "
        <div class='alert alert-danger'>
            Please complete all fields.
        </div>";
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Request Access</title>

    <!-- Bootstrap stylesheet used for the page layout and styling. -->
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

        ../index.html
            K-Globe Elevator System
        </a>

        ../index.html
            Home
        </a>

    </div>

</nav>


<!-- =========================================================
     ACCESS REQUEST FORM
     ========================================================= -->

<div class="container mt-5">

    <div class="card shadow-lg">

        <div class="card-header bg-primary text-white">

            <h2 class="mb-0">
                Request Access
            </h2>

        </div>


        <div class="card-body">

            <!-- Display any form processing messages. -->
            <?= $message ?>


            <p class="lead">
                You are not currently authenticated.
                Please enter your information below.
            </p>


            <form method="POST">


                <!-- USERNAME -->

                <div class="mb-3">

                    <label
                        for="username"
                        class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        required>

                </div>


                <!-- PASSWORD -->

                <div class="mb-3">

                    <label
                        for="password"
                        class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required>

                </div>


                <!-- FORM CONTROLS -->

                <button
                    type="submit"
                    class="btn btn-primary">
                    Request Access
                </button>

                ../index.html
                    Cancel
                </a>

            </form>

        </div>

    </div>


    <!-- =====================================================
         COPYRIGHT
         ===================================================== -->

    <div class="text-center mt-3 text-muted">

        Copyright &copy; Owen K., Leighton E., Blake G.

    </div>

</div>


</body>

</html>