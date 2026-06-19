<?php $submitted = !empty ($_POST);?>

<!DOCTYPE html>

<html>
    <head><title>Form Handler Page</title></head>
    <body>
        <p>Form submitted? <?php echo (int) $submitted; ?> </p>
        <p>Your login info is</p>
        <ul>
            <li><b>username</b>: <?php echo $_POST['username']; ?></li>
            <li><b>password</b>: <?php echo $_POST['password']; ?></li>
        </ul>
        </b>
        <p><a href="../index.html"> Home Page</a></p>
        <p>Copyright &copy; Owen K., Leighton E., Blake G.</p>
    </body>
</html>