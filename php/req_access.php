<?php $submitted = !empty ($_POST);?>
// this is where we will add users into data base

<!DOCTYPE html>

<html>
    <head><title>Form Handler Page</title></head>
    <body>
        <form action="php/authentication.php" method="post" id="login">
            <h1>You arent authenticated please input infromation below. </h1>
            <fieldset>
            <p><label>Username:
                <input type="text" name="username" id = "username"/>
            </label></p>
            <p><label>Password:
                <input type="password" name="password" id = "password" required/>
            </label></p>
        <p><a href="../index.html"> Home Page</a></p>
        <p>Copyright &copy; Owen K., Leighton E., Blake G.</p>
    </body>
</html>
