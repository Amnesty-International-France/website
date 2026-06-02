function setRequiredHiddenFields(form, status) {
  [
    '.firstname-input',
    '.lastname-input',
    '.zipcode-input',
    '.country-input',
  ].forEach((selector) => {
    const field = form.querySelector(selector);

    if (field) {
      field.required = status;
    }
  });
}

export const toggleFullFormPetition = () => {
  document.querySelectorAll('.signature-petition-form').forEach((form) => {
    const emailInput = form.querySelector('.email-input');
    const fullForm = form.querySelector('.full-form');

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
              setRequiredHiddenFields(form, false);
            } else {
              fullForm.style.display = 'flex';
              setRequiredHiddenFields(form, true);
            }
          })
          .catch();
      } else {
        fullForm.style.display = 'none';
        setRequiredHiddenFields(form, false);
      }
    });
  });
};

export const initTunnelClhForm = () => {
  const card = document.querySelector('.tunnel-clh-card');
  if (!card) return;

  const signForm = card.querySelector('.tunnel-clh-sign-form');
  if (!signForm) return;

  const emailStep = signForm.querySelector('.tunnel-clh-email-step');
  const signBtn = signForm.querySelector('[name="sign_petition"]');

  if (!signBtn) return;

  signBtn.addEventListener('click', (e) => {
    const email = card.dataset.email;

    if (email) {
      let hiddenEmail = signForm.querySelector('input[type="hidden"][name="user_email"]');
      if (!hiddenEmail) {
        hiddenEmail = document.createElement('input');
        hiddenEmail.type = 'hidden';
        hiddenEmail.name = 'user_email';
        signForm.appendChild(hiddenEmail);
      }
      hiddenEmail.value = email;
      return;
    }

    if (!emailStep || !emailStep.hidden) return;
    e.preventDefault();
    emailStep.hidden = false;
    emailStep.querySelector('input[type="email"]')?.focus();
  });
};

const getSignedPetitionIds = () => {
  try {
    const parsed = JSON.parse(localStorage.getItem('signedPetition') ?? '[]');
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
};

export const getPetitionIdForCLH = () => {
  const cookieMatch = document.cookie.match(/(?:^|; )clh_signed_petitions=([^;]*)/);
  if (!cookieMatch) return;

  try {
    const cookieIds = JSON.parse(decodeURIComponent(cookieMatch[1]));
    if (Array.isArray(cookieIds)) {
      localStorage.setItem('signedPetition', JSON.stringify(cookieIds.map(String)));
    }
  } catch {
    // cookie malformé, on ignore
  }
};

export const stepperTunnelClh = () => {
  const stepper = document.querySelector('.tunnel-clh-stepper');
  if (!stepper) return;

  const serverCount =
    stepper?.dataset.signedCount !== undefined ? parseInt(stepper?.dataset.signedCount, 10) : null;

  const localStorageIds = getSignedPetitionIds();
  const checkedCount = serverCount || localStorageIds.length;
  const limit = Math.min(checkedCount, stepper.children.length);

  Array.from(stepper.children).forEach((child) => child.classList.remove('is-checked'));

  for (let i = 0; i < limit; i++) {
    stepper.children[i].classList.add('is-checked');
  }
};

export const submitCodeOrigine = () => {
  document.querySelectorAll('.signature-petition-form').forEach((form) => {
    form.addEventListener('submit', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const codeOrigineValue =
        urlParams.get('reserved_originecode') ?? urlParams.get('code_origine');

      if (codeOrigineValue && !form.querySelector('input[name="code_origine"]')) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'code_origine';
        hiddenInput.value = codeOrigineValue;
        form.appendChild(hiddenInput);
      }
    });
  });
};
