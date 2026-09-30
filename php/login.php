<?php

/**
 * @file
 * @brief Handles and displays submitted login form information.
 *
 * Checks whether form data was submitted using the POST method and
 * displays the submitted username and password on the page.
 *
 * The page also provides a link back to the website home page.
 */

// Check whether any POST form data was submitted.
$submitted = !empty($_POST);

?>

<!DOCTYPE html>

<html>

    <head>
        <title>Form Handler Page</title>
    </head>

    <body>

        <!-- Display whether the form was submitted. -->
        <p>
            Form submitted?
            <?php echo (int) $submitted; ?>
        </p>

        <p>Your login info is</p>

        <!-- Display the submitted login information. -->
        <ul>

            <li>
                <b>username</b>:
                <?php echo $_POST['username']; ?>
            </li>

            <li>
                <b>password</b>:
                <?php echo $_POST['password']; ?>
            </li>

        </ul>

        <!-- Link back to the website home page. -->
        <p>
            ../index.htmlHome Page</a>
        </p>

        <p>
            Copyright &copy; Owen K., Leighton E., Blake G.
        </p>

    </body>

</html>