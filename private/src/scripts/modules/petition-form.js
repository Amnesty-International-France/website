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
    return JSON.parse(localStorage.getItem('signedPetition') ?? '[]');
  } catch {
    return [];
  }
};

export const getPetitionIdForCLH = () => {
  const button = document.getElementById('petition-clh');

  if (!button) return;

  const currentPetitionId = String(button.dataset.petitionId);

  if (!currentPetitionId) return;

  const signForm = button.closest('form');

  if (!signForm) return;

  signForm.addEventListener('submit', () => {
    const stored = getSignedPetitionIds();
    const updated = [...new Set([...stored.map(String), currentPetitionId])];
    localStorage.setItem('signedPetition', JSON.stringify(updated));

    const expires = new Date();
    expires.setDate(expires.getDate() + 30);
    document.cookie = `clh_signed_petitions=${encodeURIComponent(JSON.stringify(updated))};expires=${expires.toUTCString()};path=/;SameSite=Strict`;
  });
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
  const form = document.querySelector('.signature-petition-form');

  if (form) {
    form.addEventListener('submit', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const codeOrigineValue =
        urlParams.get('reserved_originecode') ?? urlParams.get('code_origine');

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
