var today = new Date();								 // new Date() object with current date and time
var year = today.getFullYear();   
var birthdateO =new Date('Dec 10, 2004 12:00:00'); // new Date() object with a value
var birthdateL = new Date('Apr 2, 1998 12:00:00');
var birthdateB = new Date('Sep 24, 2000 12:00:00');

var ageL = today.getTime() - birthdateL.getTime();	// Age in milliseconds
ageL = Math.floor(ageL / 31556900000);
msgL = '<p>My age is: ' + ageL + ' years </p>';
var elemtL = document.getElementById('infoL');
elemtL.innerHTML = msgL;

var ageO = today.getTime() - birthdateO.getTime();	// Age in milliseconds
ageO = Math.floor(ageO / 31556900000);
msgO = '<p>My age is: ' + ageO + ' years </p>';
var elemtO = document.getElementById('infoO');
elemtO.innerHTML = msgO;

var ageB = today.getTime() - birthdateB.getTime();	// Age in milliseconds
ageB = Math.floor(ageB / 31556900000);
msgB = '<p>My age is: ' + ageB + ' years </p>';
var elemtB = document.getElementById('infoB');
elemtB.innerHTML = msgB;

var ft = document.getElementById('foot');
ft.innerHTML = '<p>Copyright &copy ' + year + '</p>'; 