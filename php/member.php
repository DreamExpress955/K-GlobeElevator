<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) 
{
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Area</title>
</head>
<body>

<h2>Welcome <?php echo $_SESSION['username']; ?>!</h2>

<p>This page contains information for authorized users only.</p>
<p> THis is where we will hide all of the admin stuff </p>

logout.phpLogout</a>

</body>
</html>