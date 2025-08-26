const isEmailValid = (value) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return emailRegex.test(value);
};

const isPhoneValid = (phone) => {
  const cleaned = phone.replace(/\D/g, '');
  const regexPhone = /^(\+|00)?\d{1,4}[\s.-]?\(?\d+\)?([\s.-]?\d+)*$/;
  const minDigits = 8;

  return regexPhone.test(phone.trim()) && cleaned.length >= minDigits;
};

const throwGlobalFormMessage = (element, message, type = 'error') => {
  const formMessageDiv = document.querySelector(element);

  if (!formMessageDiv) {
    console.error(`L'élément ${element} est introuvable`);
    return;
  }

  formMessageDiv.classList.remove('hidden', 'error', 'success', 'info');
  formMessageDiv.classList.add(type);
  formMessageDiv.textContent = message;
};

const checkIfEmailExistInAmnesty = async (email) => {
  try {
    const urlForCheckEmail = 'https://www.amnesty.fr/api/inscriptions/lead/exists';
    const emailExisting = await fetch(urlForCheckEmail, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email }),
    });
    const res = await emailExisting.json();

    if (!res.ok) {
      const errorData = await res.json().catch(() => ({ message: 'Erreur inconnue' }));
      throw new Error(errorData.message || `Erreur HTTP: ${res.status}`);
    }

    return res.exists;
  } catch (e) {
    throwGlobalFormMessage('.form-mess', e.message);
    return false;
  }
};

const showAdditionalForm = async (email) => {
  const additionalForm = document.querySelector('.additional-form');

  if (!additionalForm) {
    console.error('Le formulaire additionnel est introuvable.');
    throwGlobalFormMessage('.form-mess', "'Le formulaire additionnel est introuvable.'");
    return;
  }

  try {
    const emailExisting = await checkIfEmailExistInAmnesty(email);
    if (emailExisting) {
      additionalForm.classList.remove('hidden');
    }
  } catch (error) {
    console.error("Erreur lors de la vérification de l'email :", error);
    throwGlobalFormMessage('.form-mess', error.message);
  }
};

const throwInputOnError = (input) => {
  const errorContainer =
    input && input.type === 'radio'
      ? document.querySelector('.input-error-civility')
      : input.nextElementSibling;

  if (!input || !errorContainer) return;

  const showError = (message) => {
    errorContainer.textContent = message;
    errorContainer.classList.remove('hidden');
    input.classList.add('error');
    input.classList.remove('success');
  };

  const showSuccess = () => {
    errorContainer.textContent = '';
    errorContainer.classList.add('hidden');
    input.classList.add('success');
    input.classList.remove('error');
  };

  const validate = () => {
    const value = input.value.trim();

    if (input.required && value === '') {
      showError('Ce champ est requis.');
      return false;
    }

    if (input.type === 'email' && !isEmailValid(value)) {
      showError('Merci de saisir une adresse email valide.');
      return false;
    }

    if (input.type === 'tel' && !isPhoneValid(value)) {
      showError('Merci de saisir un numéro de téléphone valide.');
      return false;
    }

    showSuccess();

    if (input.type === 'email') {
      showAdditionalForm(value);
    }

    return true;
  };

  input.addEventListener('blur', validate);
  input.addEventListener('input', validate);
};

export const urgentRegister = () => {
  const form = document.querySelector(`#urgent-register`);

  if (!form || !form?.elements) return;

  [...form.elements].forEach((formElement) => {
    throwInputOnError(formElement);
  });
};

export default {
  urgentRegister,
};
