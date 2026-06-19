var now = new Date();

// Get parts of the date
var year = now.getFullYear();
var monthIndex = now.getMonth(); // 0-11
var day = now.getDate();
var hours = now.getHours(); // 24-hour format
var minutes = now.getMinutes();

// Convert month number to name
var months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

var monthName = months[monthIndex];

// Add leading zero to minutes if needed
if (minutes < 10) {
    minutes = "0" + minutes;
}

// Build message
var msg = '<p>Current date & time: ' +
            monthName + ' ' + day + ', ' + year +
            ' — ' + hours + ':' + minutes +
            '</p>';

document.getElementById('Time').innerHTML = msg;

