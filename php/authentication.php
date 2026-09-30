<?php

/**
 * @file
 * @brief Authenticates users against the authorized users database.
 *
 * Processes a username and password submitted through a POST request
 * and checks the supplied credentials against the authorizedUsers
 * database.
 *
 * If the credentials are valid, the username is stored in the current
 * PHP session and the user is redirected to the member page. If the
 * credentials are invalid, the user is redirected to the access
 * request page.
 */

// Start or resume the current session.
session_start();

// Get the submitted username and password from the POST request.
$username = $_POST['username'];
$password = $_POST['password'];

// Database connection.
//
// Replace YOUR_DATABASE_PASSWORD with the database password used
// on the system. Do not commit the real password to source control.
$db = new PDO(
    'mysql:host=127.0.0.1;dbname=authorizedUsers',
    'myphpadmin',
    'YOUR_DATABASE_PASSWORD'
);

// Owen's local database connection.
// $db = new PDO(
//     'mysql:host=127.0.0.1;dbname=authorizedUsers',
//     'root',
//     ''
// );

// Configure PDO to return query results as associative arrays.
$db->setAttribute(
    PDO::ATTR_DEFAULT_FETCH_MODE,
    PDO::FETCH_ASSOC
);

// Set authentication to false until valid credentials are found.
$authenticated = false;

// Search the database for the submitted username.
$query = "SELECT * FROM authorizedUsers WHERE username = '$username'";
$rows = $db->query($query);

// Check the submitted credentials against the database results.
foreach ($rows as $row)
{
    echo $row['username'];

    if (
        $username === $row['username']
        && $password === $row['password']
    )
    {
        $authenticated = true;
    }
}

// Redirect the user based on the authentication result.
if ($authenticated)
{
    // Store the authenticated username in the session.
    $_SESSION['username'] = $username;

    // Redirect an authenticated user to the member page.
    header("Location: member.php");
}
else
{
    // Redirect an unauthenticated user to the access request page.
    header("Location: req_access.php");
}

?>