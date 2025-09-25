const newsletterFormElement = () => {
  const footer = document.querySelector('footer');
  const overfooter = footer?.querySelector('.over-footer');
  const formLead = overfooter?.querySelector('form[name="newsletter-lead-form"]');
  const inputFooterNL = formLead?.querySelector('input[name="newsletter-lead"]');
  const ctaFooterNL = overfooter?.querySelector('.register-nl');
  const formNewsletterPage = document.querySelector('.newsletter-form');

  return {
    footer: footer ?? null,
    overfooter: overfooter ?? null,
    inputFooterNL: inputFooterNL ?? null,
    ctaFooterNL: ctaFooterNL ?? null,
    formNewsletterPage: formNewsletterPage ?? null,
  };
};

export const isValidEmail = (value) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  if (emailRegex.test(value)) {
    return value;
  }

  return false;
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

export const redirectWithEmail = () => {
  const { inputFooterNL, ctaFooterNL } = newsletterFormElement();

  if (!inputFooterNL || !ctaFooterNL) return;

  inputFooterNL.addEventListener('input', async (event) => {
    const email = event.currentTarget?.value.trim();

    if (!isValidEmail(email)) return;

    try {
      const emailExist = (await checkIfEmailExist(email))?.exists;
      const currentHref = new URL(ctaFooterNL.getAttribute('href'), window.location.origin);

      currentHref.searchParams.set('email', email);
      ctaFooterNL.setAttribute('href', currentHref.toString());
    } catch (e) {
      console.error("Erreur lors de la vérification d'email :", e);
    }
  });
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

export const selectThemeNl = () => {
  const { formNewsletterPage } = newsletterFormElement();

  if (!formNewsletterPage) return;

  const themeChoicesNL = formNewsletterPage.querySelector('.theme-newsletter');

  if (!themeChoicesNL) return;

  themeChoicesNL.addEventListener('click', async (event) => {
    const currentTheme = event.target.closest('.form-group');
    if (!currentTheme) return;

    const divCheckBox = currentTheme.querySelector('.checkbox');
    const inputTheme = currentTheme.querySelector('input[type=checkbox]');

    if (!divCheckBox || !inputTheme) return;

    inputTheme.checked = !inputTheme.checked;

    divCheckBox.classList.toggle('checked', inputTheme.checked);
  });
};

export default { selectThemeNl, emptyInputNewsletterLead };
