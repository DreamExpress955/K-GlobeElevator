<?php $submitted = !empty ($_POST);?>

<!DOCTYPE html>

<html>
    <head><title>Form Handler Page</title></head>
    <body>
        <p>Form submitted? <?php echo (int) $submitted; ?> </p>
        <p>your information is</p>
        <ul>
            <li><b>First Name</b>: <?php echo $_POST['firstname']; ?></li>
            <li><b>Last Name</b>: <?php echo $_POST['lastname']; ?></li>
            <li><b>Who They Are </b>: <?php echo $_POST['who_are_you']; ?></li>
            <li><b>Email</b>: <?php echo $_POST['eamil']; ?></li>
            <li><b>Birth date</b>: <?php echo $_POST['email']; ?></li>
        </ul>
        </b>
        <p><a href="../index.html"> Home Page</a></p>
        <p>Copyright &copy; Owen K., Leighton E., Blake G.</p>
    </body>
</html>