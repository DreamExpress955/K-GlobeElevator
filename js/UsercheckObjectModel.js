//THis is the java script to check if the username and password are longer than 7 characters long

var elUsername = document.getElementById('username');
var elPassword = document.getElementById('password');
var elMsg = document.getElementById('feedback');
var elError = document.getElementById('feedback2');

//checks if the user name is long enough
function checkUsername(minLength) {
    if(elUsername.value.length < minLength){    //checks the legnth
        elMsg.innerHTML = '<p>Username must be ' +minLength + ' characters or more</p>';
    }
    else {
        elMsg.innerHTML = ''; //clears the screen
    }
}

//checks if the password is long enough
function checkPassword(minlength){
    if(elPassword.value.length < minlength) {
        elError.innerHTML = '<p>passowrd must be ' +minlength + 'characters or more</p>';
    }
    else{
        elError.innerHTML = ''; //clears screen
    }
}
//gives focus to the username
function fusername(){
    elUsername.focus();
}
//makes the user name textbox focused
window.addEventListener('load',fusername, false);

//checks both user name and password
elUsername.addEventListener('blur',function(){checkUsername(7)}, false);
elPassword.addEventListener('blur',function(){checkPassword(7)}, false);