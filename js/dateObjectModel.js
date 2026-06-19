var today = new Date();								 // new Date() object with current date and time
var year = today.getFullYear();   
// var OKbirthdate =new Date(); // new Date() object with a value
var birthdate = new Date('Apr 2, 1998 12:00:00');
// var BGbirthdate = new Date();

var age = today.getTime() - birthdate.getTime();	// Age in milliseconds
age = Math.floor(age / 31556900000);

msg = '<p>My age is: ' + age + ' years </p>';
var elemt = document.getElementById('info');
elemt.innerHTML = msg;

var ft = document.getElementById('foot');
ft.innerHTML = '<p>Copyright &copy ' + year + '</p>'; 