/* global UrgentRegisterData */

const notRequiredHiddenFields = (status = true) => {
  const additionFormHidden = document.querySelector('.additional-form');

  if (!additionFormHidden) return;

  const hiddenFields = additionFormHidden.querySelectorAll('input, select');

  if (!hiddenFields.length) return;

  hiddenFields.forEach((_field, index) => {
    const currentField = hiddenFields[index];
    currentField.required = status;
  });
};

const isValidEmail = (value) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

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

const checkIfEmailExist = async (email) => {
  try {
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

    if (!res.ok) {
      const errorData = await res.json().catch(() => ({ message: 'Erreur inconnue' }));
      return throwGlobalFormMessage(
        '.form-mess',
        errorData.message || `Erreur HTTP: ${res.status}`,
      );
    }

    return res.json();
  } catch (e) {
    throwGlobalFormMessage('.form-mess', e.message, e.status);
    return false;
  }
};

const disabledSubmit = (form) => {
  if (!form) return true;

  return [...form.elements].some((field) => field.required && !field.value.trim());
};

const showAdditionalForm = async (email) => {
  const additionalForm = document.querySelector('.additional-form');
  const form = document.querySelector(`#urgent-register`);
  const submitButton = form.querySelector('button[type="submit"]');

  if (!additionalForm || !form || !submitButton) {
    console.error('Élément de formulaire manquant (form, additionalForm, ou submitButton).');
    return;
  }

  try {
    const emailExisting = await checkIfEmailExist(email);

    const showAdditional = emailExisting && emailExisting.exists === false;

    additionalForm.classList.toggle('hidden', !showAdditional);
    notRequiredHiddenFields(showAdditional);

    if (showAdditional === false) {
      const telFields = form.querySelectorAll('input[name="tel"]');

      telFields.forEach((field) => {
        const currentField = field;
        currentField.required = false;

        const errorContainer = currentField.nextElementSibling;
        if (errorContainer && errorContainer.classList.contains('input-error')) {
          errorContainer.classList.add('hidden');
          currentField.classList.remove('error');
          currentField.classList.remove('success');
        }
      });
    }

    submitButton.disabled = disabledSubmit(form);
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

  const submitButton = document.querySelector('button[type="submit"]');

  if (!input || !errorContainer || !submitButton) return;

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

  const validate = async () => {
    const value = input.value.trim();

    if (input.required && value === '') {
      showError('Ce champ est requis.');
      submitButton.disabled = true;
      return false;
    }

    if (input.type === 'tel' && input.required && !isPhoneValid(value)) {
      showError('Merci de saisir un numéro de téléphone valide.');
      return false;
    }

    if (input.type === 'email' && !isValidEmail(value)) {
      showError('Merci de saisir une adresse email valide.');
      return false;
    }

    showSuccess();

    if (input.type === 'email' && isValidEmail(value)) {
      await showAdditionalForm(value);
    }

    return true;
  };

  input.addEventListener('blur', validate);
  input.addEventListener('input', validate);
};

const urgentRegister = () => {
  const form = document.querySelector(`#urgent-register`);

  if (!form || !form?.elements) return;

  notRequiredHiddenFields(false);

  const submitButton = form.querySelector('button[type="submit"]');

  if (!submitButton) return;

  const formFields = [...form.elements];

  formFields.forEach((formElement) => {
    throwInputOnError(formElement);
  });

  submitButton.disabled = disabledSubmit(form);

  form.addEventListener('input', () => {
    submitButton.disabled = disabledSubmit(form);
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = new FormData();

    formFields.filter((field) => {
      if ((field.required && field.value.trim() !== '') || field.name === 'type') {
        formData.append(field.name, field.value);
        return true;
      }
      return false;
    });

    submitButton.disabled = disabledSubmit(form);

    try {
      const headers = {};

      Object.assign(
        headers,
        UrgentRegisterData.is_connected
          ? { 'X-WP-Nonce': UrgentRegisterData.nonce }
          : { 'X-Amnesty-UA-Nonce': UrgentRegisterData.nonce },
      );

      const response = await fetch(UrgentRegisterData.url, {
        method: 'POST',
        body: formData,
        headers,
      });

      const result = await response.json();
      throwGlobalFormMessage('.form-mess', result.message, result.status);
    } catch (e) {
      throwGlobalFormMessage('.form-mess', e.message, e.status);
    }
  });
};

export default urgentRegister;
