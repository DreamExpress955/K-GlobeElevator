//
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
el.addEventListener('keypress', charCount, false);