/**
 * @file
 * @brief Validates the username and password fields on the login form.
 *
 * Checks whether the username and password meet the required minimum
 * length. Feedback messages are displayed when either value is too short.
 * The username field is also automatically focused when the page loads.
 *
 * @author Blake Gergely
 */

// Get the username, password, and feedback elements.
var elUsername = document.getElementById('username');
var elPassword = document.getElementById('password');
var elMsg = document.getElementById('feedback');
var elError = document.getElementById('feedback2');

/**
 * @brief Checks whether the username meets the minimum length.
 *
 * Displays a feedback message if the username contains fewer characters
 * than the required minimum. The feedback message is cleared when the
 * username is long enough.
 *
 * @param {number} minLength Minimum number of characters required.
 */
function checkUsername(minLength) {
    if (elUsername.value.length < minLength) {
        elMsg.innerHTML =
            '<p>Username must be ' + minLength + ' characters or more</p>';
    }
    else {
        elMsg.innerHTML = '';
    }
}

/**
 * @brief Checks whether the password meets the minimum length.
 *
 * Displays a feedback message if the password contains fewer characters
 * than the required minimum. The feedback message is cleared when the
 * password is long enough.
 *
 * @param {number} minLength Minimum number of characters required.
 */
function checkPassword(minLength) {
    if (elPassword.value.length < minLength) {
        elError.innerHTML =
            '<p>Password must be ' + minLength + ' characters or more</p>';
    }
    else {
        elError.innerHTML = '';
    }
}

/**
 * @brief Gives keyboard focus to the username field.
 *
 * Called when the webpage loads so the user can immediately begin
 * entering a username.
 */
function fusername() {
    elUsername.focus();
}

// Give focus to the username field when the page loads.
window.addEventListener('load', fusername, false);

// Check the username when the user leaves the username field.
elUsername.addEventListener(
    'blur',
    function() {
        checkUsername(7);
    },
    false
);

// Check the password when the user leaves the password field.
elPassword.addEventListener(
    'blur',
    function() {
        checkPassword(7);
    },
    false
);