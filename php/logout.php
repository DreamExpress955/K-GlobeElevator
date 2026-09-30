<?php

/**
 * @file
 * @brief Logs the current user out of the website.
 *
 * Starts the current PHP session, destroys the session data,
 * and redirects the user back to the login page.
 */

// Start or resume the current session.
session_start();

// Destroy the current session and log the user out.
session_destroy();

// Redirect the user back to the login page.
header("Location: login.php");

// Stop script execution after the redirect.
exit();

?>