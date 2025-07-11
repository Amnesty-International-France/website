const toggleFullFormPetition = () => {
  const emailInput = document.querySelector('.email-input');
  const fullForm = document.querySelector('.full-form');

  if (!emailInput || !fullForm) return;

  emailInput.addEventListener('input', () => {
    const emailValue = emailInput.value.trim();
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);

    if (isValid) {
      fullForm.style.display = 'flex';
    } else {
      fullForm.style.display = 'none';
    }
  });
};

export default toggleFullFormPetition;
