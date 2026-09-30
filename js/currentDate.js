/**
 * @file
 * @brief Displays the current date and time on the webpage.
 *
 * Gets the current date and time from the user's computer, converts
 * the month number into its corresponding month name, formats the
 * time, and displays the result in the HTML element with the ID "Time".
 */

// Get the current date and time.
var now = new Date();

// Get the individual parts of the date and time.
var year = now.getFullYear();
var monthIndex = now.getMonth(); // Month index ranges from 0 to 11.
var day = now.getDate();
var hours = now.getHours();      // 24-hour format.
var minutes = now.getMinutes();

// Month names used to convert the numeric month into text.
var months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

var monthName = months[monthIndex];

// Add a leading zero when the minute value is less than 10.
if (minutes < 10) {
    minutes = "0" + minutes;
}

// Create the formatted date and time message.
var msg = '<p>Current date & time: ' +
          monthName + ' ' + day + ', ' + year +
          ' — ' + hours + ':' + minutes +
          '</p>';

// Display the date and time on the webpage.
document.getElementById('Time').innerHTML = msg;