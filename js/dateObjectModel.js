/**
 * @file
 * @brief Calculates and displays ages and the current copyright year.
 *
 * Uses the current date to calculate the ages of three people based on
 * their stored birth dates. Each calculated age is displayed in its
 * corresponding HTML element. The current year is also displayed in
 * the website footer.
 */

// Get the current date and year.
var today = new Date();
var year = today.getFullYear();

// Store the birth dates used for age calculations.
var birthdateO = new Date('Dec 10, 2004 12:00:00');
var birthdateL = new Date('Apr 2, 1998 12:00:00');
var birthdateB = new Date('Sep 24, 2000 12:00:00');

// Calculate and display the age for L.
var ageL = today.getTime() - birthdateL.getTime();
ageL = Math.floor(ageL / 31556900000);

var msgL = '<p>My age is: ' + ageL + ' years </p>';
var elemtL = document.getElementById('infoL');
elemtL.innerHTML = msgL;

// Calculate and display the age for O.
var ageO = today.getTime() - birthdateO.getTime();
ageO = Math.floor(ageO / 31556900000);

var msgO = '<p>My age is: ' + ageO + ' years </p>';
var elemtO = document.getElementById('infoO');
elemtO.innerHTML = msgO;

// Calculate and display the age for B.
var ageB = today.getTime() - birthdateB.getTime();
ageB = Math.floor(ageB / 31556900000);

var msgB = '<p>My age is: ' + ageB + ' years </p>';
var elemtB = document.getElementById('infoB');
elemtB.innerHTML = msgB;

// Display the current year in the website footer.
var ft = document.getElementById('foot');
ft.innerHTML = '<p>Copyright &copy; ' + year + '</p>';