function checkPasswordMatch() {

    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm-password").value;

    if (password !== confirmPassword) {
        document.getElementById("password-error-not-match").style.display = "block";
        document.getElementById("submit-btn").disabled = true;
    } else {
        document.getElementById("password-error-not-match").style.display = "none";
        document.getElementById("submit-btn").disabled = false;
    }
}

function checkPassphraseStrength() {
    const input = document.getElementById('password');
    const passphrase = input.value;

    const lengthCheck = passphrase.length >= 6;
    const uppercaseCheck = /[A-Z]/.test(passphrase);
    const lowercaseCheck = /[a-z]/.test(passphrase);
    const numberCheck = /\d/.test(passphrase);
    const specialCheck = /[!@#$%^&*(),.?":{}|<>]/.test(passphrase);

    if (!(lengthCheck && uppercaseCheck && lowercaseCheck &&
        numberCheck && specialCheck)) {
        const elem = document.getElementById("password-error-too-weak");
        elem.style.display = "block";
        elem.classList.add("aif-input-error");
        input.setAttribute("aria-describedby", "passwordHelp password-error-too-weak");
        document.getElementById("submit-btn").disabled = true;
    } else {
        input.removeAttribute("aria-describedby");
        input.setAttribute("aria-described-by", "passwordHelp");
        document.getElementById("password-error-too-weak").style.display = "none";
        document.getElementById("submit-btn").disabled = false;
    }

}

