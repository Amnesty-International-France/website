const newsletterFormElement = () => {
  const footer = document.querySelector('footer');
  const overfooter =
    footer?.querySelector('.over-footer') ?? document.querySelector('.over-footer');
  const formLead = overfooter?.querySelector('form[name="newsletter-lead-form"]');
  const inputFooterNL = formLead?.querySelector('input[name="newsletter-lead"]');
  const ctaFooterNL = overfooter?.querySelector('.register-nl');
  const formNewsletterPage = document.querySelector('.newsletter-form');

  return {
    footer: footer ?? null,
    overfooter: overfooter ?? null,
    inputFooterNL: inputFooterNL ?? null,
    formLead: formLead ?? null,
    ctaFooterNL: ctaFooterNL ?? null,
    formNewsletterPage: formNewsletterPage ?? null,
  };
};

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

export const isValidEmail = (value) => {
  if (EMAIL_REGEX.test(value)) {
    return value;
  }

  return false;
};

export const emptyInputNewsletterLead = () => {
  const { inputFooterNL, ctaFooterNL } = newsletterFormElement();

  if (!inputFooterNL || !ctaFooterNL) return;

  const updateButtonState = () => {
    const hasValue = inputFooterNL.value.trim().length > 0;
    ctaFooterNL.disabled = !(hasValue && isValidEmail(inputFooterNL.value.trim()));
  };

  updateButtonState();

  inputFooterNL.addEventListener('input', updateButtonState);
};

export const handleNewsletterSubmission = () => {
  const { formLead, ctaFooterNL } = newsletterFormElement();

  if (!formLead || !ctaFooterNL) return;

  const buttonText = ctaFooterNL.querySelector('.button-text');
  const spinner = ctaFooterNL.querySelector('.spinner');

  formLead.addEventListener('submit', (event) => {
    if (ctaFooterNL.disabled) {
      event.preventDefault();
      return;
    }
    ctaFooterNL.disabled = true;

    if (buttonText) {
      buttonText.classList.add('hidden');
    }
    if (spinner) {
      spinner.classList.remove('hidden');
    }
  });
};

export default { emptyInputNewsletterLead, handleNewsletterSubmission };
