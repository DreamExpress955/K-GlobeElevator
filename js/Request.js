/**
 * @file
 * @brief Handles message character limits and form validation.
 *
 * Limits the message text area to 180 characters and displays the
 * number of characters remaining. This file also validates the access
 * form to make sure the required name, email, and user type fields
 * have been completed before the form is submitted.
 *
 * @author Blake Gergely
 */


/* ============================================================
 * Message Character Counter
 * ============================================================ */

var el;
var maxChars = 180;

/**
 * @brief Limits the message length and updates the character counter.
 *
 * Checks the number of characters entered in the message text area.
 * If the message exceeds the 180-character limit, the extra characters
 * are removed. The remaining character count is displayed on the page.
 *
 * The character counter changes to red when 20 or fewer characters
 * remain.
 *
 * @param {Event} e The input event that triggered the function.
 */
function charCount(e) {
    var textEntered, charDisplay, counter;

    textEntered = document.getElementById('message').value;
    charDisplay = document.getElementById('charactersLeft');

    // Prevent the message from exceeding the maximum character limit.
    if (textEntered.length > maxChars) {
        el.value = textEntered.substring(0, maxChars);
        textEntered = el.value;
    }

    // Calculate the number of characters remaining.
    counter = maxChars - textEntered.length;

    // Change the counter to red when 20 or fewer characters remain.
    if (counter <= 20) {
        charDisplay.style.color = "red";
    }
    else {
        charDisplay.style.color = "black";
    }

    // Display the number of remaining characters.
    charDisplay.innerHTML =
        '<p>Characters remaining: ' + counter + '</p>';
}

// Monitor the message field as the user types.
el = document.getElementById('message');
el.addEventListener('input', charCount, false);


/* ============================================================
 * Form Validation
 * ============================================================ */

var elForm;

var firstnamefeedback;
var lastnamefeedback;
var emailfeedback;
var whofeedback;

var firstNameinput;
var lastNameinput;
var emailinput;

// Get the form and feedback elements.
elForm = document.getElementById('access');

firstnamefeedback = document.getElementById('firstnamefeedback');
lastnamefeedback = document.getElementById('lastnamefeedback');
emailfeedback = document.getElementById('emailfeedback');
whofeedback = document.getElementById('whofeedback');

// Get the form input elements.
firstnameinput = document.getElementById('firstname');
lastnameinput = document.getElementById('lastname');
emailinput = document.getElementById('email');

/**
 * @brief Checks whether the first name field has been completed.
 *
 * Displays a feedback message and prevents the form from being submitted
 * if the first name field is empty.
 *
 * @param {Event} event The form submission event.
 */
function checkfirstname(event) {
    if (firstnameinput.value.length < 1) {
        firstnamefeedback.innerHTML =
            '<p>You Must Fill Out Your First Name</p>';

        event.preventDefault();
    }
    else {
        firstnamefeedback.innerHTML = '';
    }
}

/**
 * @brief Checks whether the last name field has been completed.
 *
 * Displays a feedback message and prevents the form from being submitted
 * if the last name field is empty.
 *
 * @param {Event} event The form submission event.
 */
function checklastname(event) {
    if (lastnameinput.value.length < 1) {
        lastnamefeedback.innerHTML =
            '<p>You Must Fill Out Your Last Name</p>';

        event.preventDefault();
    }
    else {
        lastnamefeedback.innerHTML = '';
    }
}

/**
 * @brief Checks whether a faculty or student option has been selected.
 *
 * Prevents the form from being submitted if neither the faculty nor
 * student option is selected.
 *
 * @param {Event} event The form submission event.
 */
function checkwho(event) {
    let faculty = document.getElementById('who_faculty').checked;
    let student = document.getElementById('who_student').checked;

    if (!faculty && !student) {
        whofeedback.innerHTML =
            '<p>Please select faculty or student</p>';

        event.preventDefault();
    }
    else {
        whofeedback.innerHTML = '';
    }
}

/**
 * @brief Checks whether the email field has been completed.
 *
 * Displays a feedback message and prevents the form from being submitted
 * if the email field is empty.
 *
 * @param {Event} event The form submission event.
 */
function checkemail(event) {
    if (emailinput.value.length < 1) {
        emailfeedback.innerHTML =
            '<p>Please fill out your email</p>';

        event.preventDefault();
    }
    else {
        emailfeedback.innerHTML = '';
    }
}

// Validate all required fields when the form is submitted.
elForm.addEventListener(
    'submit',
    function(event) {
        checkfirstname(event);
        checklastname(event);
        checkwho(event);
        checkemail(event);
    },
    false
);