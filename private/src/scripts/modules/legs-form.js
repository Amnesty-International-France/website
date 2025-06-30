const initLegsForm = () => {
  const form = document.getElementById('legsForm');
  if (!form) {
    return;
  }

  const clearFieldError = (fieldId) => {
    const errorContainer = document.getElementById(`error-${fieldId}`);
    if (errorContainer) {
      errorContainer.innerHTML = '';
      errorContainer.classList.remove('is-visible');
    }
  };

  const textFields = ['last_name', 'first_name', 'address', 'zip_code', 'city', 'email', 'phone'];
  textFields.forEach((fieldId) => {
    const inputElement = form.elements[fieldId];
    if (inputElement) {
      inputElement.addEventListener('input', () => clearFieldError(fieldId));
    }
  });

  const receiveMailCheckbox = form.elements.receive_by_mail;
  const receiveEmailCheckbox = form.elements.receive_by_email;
  const receiveOptionsErrorId = 'receive_options';

  if (receiveMailCheckbox) {
    receiveMailCheckbox.addEventListener('change', () => {
      if (receiveMailCheckbox.checked || receiveEmailCheckbox.checked) {
        clearFieldError(receiveOptionsErrorId);
      }
    });
  }
  if (receiveEmailCheckbox) {
    receiveEmailCheckbox.addEventListener('change', () => {
      if (receiveMailCheckbox.checked || receiveEmailCheckbox.checked) {
        clearFieldError(receiveOptionsErrorId);
      }
    });
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    const errors = {};

    const lastName = form.elements.last_name.value.trim();
    const firstName = form.elements.first_name.value.trim();
    const address = form.elements.address.value.trim();
    const zipCode = form.elements.zip_code.value.trim();
    const city = form.elements.city.value.trim();
    const email = form.elements.email.value.trim();
    const phone = form.elements.phone.value.trim();

    const civilityElement = form.querySelector('input[name="civility"]:checked');
    const civility = civilityElement ? civilityElement.value : '';

    const receiveByMail = form.elements.receive_by_mail.checked;
    const receiveByEmail = form.elements.receive_by_email.checked;

    document.querySelectorAll('.error-message-container').forEach((el) => {
      // eslint-disable-next-line no-param-reassign
      el.innerHTML = '';
      // eslint-disable-next-line no-param-reassign
      el.classList.remove('is-visible');
    });

    const globalFormMessages = document.getElementById('formMessages');
    if (globalFormMessages) {
      globalFormMessages.innerHTML = '';
    }

    if (!lastName) {
      errors.last_name = 'Le nom est obligatoire.';
    }
    if (!firstName) {
      errors.first_name = 'Le prénom est obligatoire.';
    }
    if (!address) {
      errors.address = "L'adresse est obligatoire.";
    }
    if (!zipCode) {
      errors.zip_code = 'Le code postal est obligatoire.';
    } else if (!/^\d{5}$/.test(zipCode)) {
      errors.zip_code = 'Le code postal doit contenir 5 chiffres.';
    }
    if (!city) {
      errors.city = 'La ville est obligatoire.';
    }

    if (!email) {
      errors.email = "L'email est obligatoire.";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errors.email = "L'adresse email n'est pas valide.";
    }

    if (!receiveByMail && !receiveByEmail) {
      errors.receive_options =
        'Veuillez choisir de recevoir la brochure par courrier postal ou par email.';
    }

    if (Object.keys(errors).length > 0) {
      Object.keys(errors).forEach((key) => {
        const errorContainer = document.getElementById(`error-${key}`);
        if (errorContainer) {
          const errorMessageParagraph = document.createElement('p');
          errorMessageParagraph.classList.add('error-message');
          errorMessageParagraph.textContent = errors[key];
          errorContainer.appendChild(errorMessageParagraph);
          errorContainer.classList.add('is-visible');
        }
      });
      return;
    }

    let body = `Civilité: ${civility || 'Non spécifié'}\n`;
    body += `Nom: ${lastName}\n`;
    body += `Prénom: ${firstName}\n`;
    body += `Adresse: ${address}\n`;
    body += `Code Postal: ${zipCode}\n`;
    body += `Ville: ${city}\n`;
    body += `Email: ${email}\n`;
    body += `Téléphone: ${phone || 'Non spécifié'}\n`;
    body += `Recevoir par courrier: ${receiveByMail ? 'Oui' : 'Non'}\n`;
    body += `Recevoir par email: ${receiveByEmail ? 'Oui' : 'Non'}\n`;

    const to = 'comnum@amnesty.fr';
    const subject = 'Demande de brochure d’informations sur les legs, donations et assurances-vie';

    const mailtoLink = `mailto:${encodeURIComponent(to)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;

    form.reset();
  });
};

export default initLegsForm;
