//the character cap for the text area
//created by Blake Gergely
var el;
var maxChars = 180;

function charCount(e) {
    var textEntered, charDisplay, counter;
    textEntered = document.getElementById('message').value;
    charDisplay = document.getElementById('charactersLeft');
    //check if there are more than the limited number of characters
    if(textEntered.length > maxChars){
        el.value = textEntered.substring(0, maxChars);
        textEntered = el.value;
    }

    counter = (maxChars - (textEntered.length));

    if (counter <= 20) {    //change to red when low, idk why but i had time
        charDisplay.style.color = "red";
    }
    else{
        charDisplay.style.color = "black";
    }
    //display the remaining characters
    charDisplay.innerHTML = '<p>Characters remaining: ' + counter + '</p>';
}

el = document.getElementById('message');
el.addEventListener('input', charCount, false);

//the Form validation
//created Blake Gergey
var elForm;
var firstnamefeedback, lastnamefeedback, emailfeedback;
var firstNameinput, lastNameinput, emailinput;

elForm = document.getElementById('access');
firstnamefeedback = document.getElementById('firstnamefeedback');
lastnamefeedback = document.getElementById('lastnamefeedback');
emailfeedback = document.getElementById('emailfeedback');
whofeedback = document.getElementById('whofeedback')

firstnameinput = document.getElementById('firstname');
lastnameinput = document.getElementById('lastname');
emailinput = document.getElementById('email');


function checkfirstname(event){
    if(firstnameinput.value.length < 1){
        firstnamefeedback.innerHTML = '<p> You Must Fill Out Your First Name </p>';
        event.preventDefault();
    }
    else{
        firstnamefeedback.innerHTML = '';
    }
}
function checklastname(event){
    if(lastnameinput.value.length < 1){
        lastnamefeedback.innerHTML = '<p> You Must Fill Out Your Last Name </p>';
        event.preventDefault();
    }
    else{
        lastnamefeedback.innerHTML = '';
    }
}
function checkwho(event){
    let faculty = document.getElementById('who_faculty').checked;
    let student = document.getElementById('who_student').checked;

    if(!faculty && !student){
        whofeedback.innerHTML = '<p>Please select faculty or student</p>';
        event.preventDefault();
    } else {
        whofeedback.innerHTML = '';
    }
}
function checkemail(event){
    if(emailinput.value.length < 1){
        emailfeedback.innerHTML = '<p> please fill out your email </p>';
        event.preventDefault();
    }
    else{
        emailfeedback.innerHTML = '';
    }
}
//create the even listeners
elForm.addEventListener('submit', function(event) {checkfirstname(event); checklastname(event); checkwho(event); checkemail(event);}, false);