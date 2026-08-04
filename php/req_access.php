<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../php/databaseFunctions.php';

$host = '127.0.0.1';
$database = 'authorizedUsers';
$user = 'root';
$password = '';

$path = "mysql:host=$host;dbname=$database";

$db = connect($path, $user, $password);
$db = connect($path, $user, $password);

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $username = trim($_POST['username']);
    $userPassword = trim($_POST['password']);

    if (!empty($username) && !empty($userPassword))
    {
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
header("Location: ../login.html");
exit();

        $message =
        "<div class='alert alert-success'>
            Access request submitted successfully.
        </div>";
    }
    else
    {
        $message =
        "<div class='alert alert-danger'>
            Please complete all fields.
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Request Access</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

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

<div class="container mt-5">

    <div class="card shadow-lg">

        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Request Access</h2>
        </div>

        <div class="card-body">

            <?= $message ?>

            <p class="lead">
                You are not currently authenticated.
                Please enter your information below.
            </p>

            <form method="POST">

                <div class="mb-3">
                    <label for="username" class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required>
                </div>

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

    <div class="text-center mt-3 text-muted">
        Copyright &copy; Owen K., Leighton E., Blake G.
    </div>

</div>

</body>
</html>