function setRequiredHiddenFields(status) {
  document.querySelector('.firstname-input').required = status;
  document.querySelector('.lastname-input').required = status;
  document.querySelector('.zipcode-input').required = status;
  document.querySelector('.country-input').required = status;
}

export const toggleFullFormPetition = () => {
  const emailInput = document.querySelector('.email-input');
  const fullForm = document.querySelector('.full-form');

  if (!emailInput || !fullForm) return;

  emailInput.addEventListener('input', () => {
    const emailValue = emailInput.value.trim();
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);

    if (isValid) {
      const formData = new URLSearchParams();
      formData.append('email', emailValue);

      fetch('/wp-json/humanity/v1/check-email', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error();
          }
          return response.json();
        })
        .then((data) => {
          if (data.exists) {
            fullForm.style.display = 'none';
            setRequiredHiddenFields(false);
          } else {
            fullForm.style.display = 'flex';
            setRequiredHiddenFields(true);
          }
        })
        .catch();
    } else {
      fullForm.style.display = 'none';
      setRequiredHiddenFields(false);
    }
  });
};

export const submitCodeOrigine = () => {
  const form = document.querySelector('.signature-petition-form');

  if (form) {
    form.addEventListener('submit', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const codeOrigineValue = urlParams.get('code_origine');

      if (codeOrigineValue) {
        if (!form.querySelector('input[name="code_origine"]')) {
          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'code_origine';
          hiddenInput.value = codeOrigineValue;
          form.appendChild(hiddenInput);
        }
      }
    });
  }
};
