
function preventCopyPaste(event) {
    event.preventDefault();
}

function checkPasswordMatch() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm-password").value;

    if (password !== confirmPassword) {
        document.getElementById("password-error").style.display = "block";
        document.getElementById("submit-btn").disabled = true;
    } else {
        document.getElementById("password-error").style.display = "none";
        document.getElementById("submit-btn").disabled = false;
    }
}

