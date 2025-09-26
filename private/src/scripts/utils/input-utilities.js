export const debounce = (cb, delay = 5000) => {
  let debounceTimer;

  return (...args) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => cb(...args), delay);
  };
};

export const isValidEmail = (value) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  if (emailRegex.test(value)) {
    return value;
  }

  return false;
};

export const isPhoneValid = (phone) => {
  const cleaned = phone.replace(/\D/g, '');
  const regexPhone = /^(\+|00)?\d{1,4}[\s.-]?\(?\d+\)?([\s.-]?\d+)*$/;
  const minDigits = 8;

  return regexPhone.test(phone.trim()) && cleaned.length >= minDigits;
};

export const throwGlobalFormMessage = (element, message, type = 'error') => {
  const formMessageDiv = document.querySelector(element);

  if (!formMessageDiv) {
    console.error(`L'élément ${element} est introuvable`);
    return;
  }

  formMessageDiv.classList.remove('hidden', 'error', 'success', 'info');
  formMessageDiv.classList.add(type);
  formMessageDiv.textContent = message;
};

export const checkIfEmailExist = async (email) => {
  try {
    const emailValid = isValidEmail(email);

    if (!emailValid) return false;

    const urlForCheckEmail = '/wp-json/humanity/v1/check-email';
    const emailExisting = await fetch(urlForCheckEmail, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        email,
      }),
    });
    const res = await emailExisting;

    return res.json();
  } catch (e) {
    return console.error("Erreur lors de la vérification d'email :", e);
  }
};
