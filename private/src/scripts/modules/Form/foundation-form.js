const initFoundationForm = () => {
  const form = document.getElementById('foundationForm');

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

  const textFields = ['last_name', 'first_name', 'email', 'phone', 'message'];
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
    const email = form.elements.email.value.trim();
    const phone = form.elements.phone.value.trim();
    const message = form.elements.message.value.trim();

    const civilityElement = form.querySelector('input[name="civility"]:checked');
    const civility = civilityElement ? civilityElement.value : '';

    const receiveByMail = form.elements.receive_by_mail.checked;

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
    if (!email) {
      errors.email = "L'email est obligatoire.";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errors.email = "L'adresse email n'est pas valide.";
    }
    if (!receiveByMail) {
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

    const gtmType = this.getAttribute('data-gtm-type');
    const gtmName = this.getAttribute('data-gtm-name');
    window.dataLayer.push({
      event: 'form_submit',
      type: gtmType,
      name: gtmName,
    });

    let body = `Civilité: ${civility || 'Non spécifié'}\n`;
    body += `Nom: ${lastName}\n`;
    body += `Prénom: ${firstName}\n`;
    body += `Email: ${email}\n`;
    body += `Téléphone: ${phone || 'Non spécifié'}\n`;
    body += `Message: ${message || 'Non spécifié'}\n`;
    body += `Recevoir par courrier: ${receiveByMail ? 'Oui' : 'Non'}\n`;

    const to = 'mdjelic@amnesty.fr';
    const subject = "Demande d'information sur la fondation";

    window.location.href = `mailto:${encodeURIComponent(to)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

    form.reset();
  });
};

export default initFoundationForm;
