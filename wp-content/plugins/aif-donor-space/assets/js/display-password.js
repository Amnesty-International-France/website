const toggleButtons = document.querySelectorAll('.aif-password-container__button');

toggleButtons.forEach(button => {
    button.addEventListener('click', function () {
        const targetId = button.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Change the button text and aria-label for accessibility
        if (type === 'password') {
            button.textContent = 'Afficher';
            button.setAttribute('aria-label', 'Afficher le mot de passe');
        } else {
            button.textContent = 'Masquer';
            button.setAttribute('aria-label', 'Masquer le mot de passe');
        }
    });
});