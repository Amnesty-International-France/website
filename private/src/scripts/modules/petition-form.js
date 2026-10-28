import { isValidEmail } from './newsletter';

// État de la saisie de l'e-mail par formulaire (les champs d'identité sont
// toujours affichés, aucune vérification n'est demandée au serveur).
// Valeur : { status: 'pending' | 'done', pendingSubmit: (() => void) | null }
// Partagé entre toggleFullFormPetition (qui lance la vérif) et initTunnelClhForm :
// si l'utilisateur tente de signer pendant que la vérif est en cours, on
// mémorise son intention dans pendingSubmit pour la rejouer à la résolution.
const emailCheckStates = new WeakMap();

function setRequiredHiddenFields(form, status) {
  ['.firstname-input', '.lastname-input', '.zipcode-input', '.country-input'].forEach(
    (selector) => {
      const field = form.querySelector(selector);

      if (field) {
        field.required = status;
      }
    },
  );
}

export const toggleFullFormPetition = () => {
  document
    .querySelectorAll('.signature-petition-form, .tunnel-clh-sign-form-anonymous')
    .forEach((form) => {
      const emailInput = form.querySelector('.email-input');
      const fullForm = form.querySelector('.full-form');

      if (!emailInput || !fullForm) return;

      const resolveCheck = () => {
        const { pendingSubmit } = emailCheckStates.get(form) ?? {};
        emailCheckStates.set(form, { status: 'done', pendingSubmit: null });
        pendingSubmit?.();
      };

      emailInput.addEventListener('input', () => {
        const emailValue = emailInput.value.trim();

        if (!isValidEmail(emailValue)) {
          emailCheckStates.delete(form);
          fullForm.style.display = 'none';
          setRequiredHiddenFields(form, false);
          return;
        }

        // The identity fields are always shown: whether the address already
        // signed is never asked to the server. Known signers keep their data.
        fullForm.style.display = 'flex';
        setRequiredHiddenFields(form, true);
        resolveCheck();
      });

      // Email kept by the server from a rejected tunnel signature: show the identity
      // fields as if it had just been typed. Petition pages only get browser-restored values, left as they are.
      if (form.matches('.tunnel-clh-sign-form-anonymous') && emailInput.value.trim()) {
        emailInput.dispatchEvent(new Event('input'));
      }
    });
};

export const scrollToTunnelClhPetition = () => {
  const container = document.getElementById('tunnel-clh-petition');
  if (!container) return;

  const scrollToPetition = () => {
    if (!window.matchMedia('(max-width: 767px)').matches) return;
    // Leave the visitor in place if they moved or started typing before `load`.
    if (window.scrollY > 0 || document.activeElement?.matches('input, select, textarea')) return;

    container.scrollIntoView({ behavior: 'instant', block: 'start' });
  };

  // Wait for images to settle before positioning the next petition after a reload.
  if (document.readyState === 'complete') {
    scrollToPetition();
  } else {
    window.addEventListener('load', scrollToPetition, { once: true });
  }
};

export const initTunnelClhForm = () => {
  document.querySelectorAll('.page-tunnel-clh-card').forEach((card) => {
    const signForm = card.querySelector('.tunnel-clh-sign-form, .tunnel-clh-sign-form-anonymous');
    if (!signForm) return;

    const signBtn = card.querySelector(`[form="${signForm.id}"][name="sign_petition"]`);
    const mobileSignCta = card.querySelector(`[data-sign-form="${signForm.id}"]`);

    if (!signBtn) return;

    const pending = new Set();
    const status = document.createElement('span');
    status.className = 'screen-reader-text';
    status.setAttribute('role', 'status');
    signBtn.after(status);

    const setPending = (reason, active) => {
      if (active) pending.add(reason);
      else pending.delete(reason);

      const busy = pending.size > 0;
      signBtn.classList.toggle('is-loading', busy);
      signBtn.setAttribute('aria-busy', String(busy));
      // Keep the named submitter enabled so sign_petition is included in the POST.
      signBtn.setAttribute('aria-disabled', String(busy));
      mobileSignCta?.setAttribute('aria-disabled', String(busy));
      status.textContent = busy ? 'Traitement en cours, veuillez patienter.' : '';
    };

    signForm.addEventListener('aif:turnstile-state', (event) => {
      setPending('security', event.detail.pending);
    });

    window.addEventListener('pageshow', (event) => {
      if (!event.persisted) return;
      pending.clear();
      const state = emailCheckStates.get(signForm);
      if (state) state.pendingSubmit = null;
      setPending('submission', false);
    });

    const submitForm = () => {
      if (signForm.requestSubmit) {
        signForm.requestSubmit(signBtn);
        return;
      }
      signBtn.click();
    };

    const prepareSignature = () => {
      const emailInput = signForm.querySelector('input[name="user_email"]');
      const email = emailInput?.value.trim();

      if (email) {
        const state = emailCheckStates.get(signForm);
        if (state?.status === 'pending') {
          setPending('email', true);
          emailCheckStates.set(signForm, {
            ...state,
            pendingSubmit: () => {
              setPending('email', false);
              submitForm();
            },
          });
          return false;
        }
        return true;
      }

      const emailStep = signForm.querySelector('.tunnel-clh-email-step');
      if (!emailStep) return true;

      emailStep.querySelector('input[type="email"]')?.focus();
      return false;
    };

    signBtn.addEventListener('click', (e) => {
      if (pending.size > 0 || !prepareSignature()) {
        e.preventDefault();
      }
    });

    signForm.addEventListener('submit', (event) => {
      if (event.defaultPrevented) return;
      if (pending.has('submission') || !prepareSignature()) {
        event.preventDefault();
        return;
      }
      setPending('submission', true);
      // The page normally unloads first; free the button if the POST never completes.
      window.setTimeout(() => setPending('submission', false), 15000);
    });

    // Editing the email cancels a queued signature; the visitor can submit again.
    signForm.querySelector('.email-input')?.addEventListener('input', () => {
      const state = emailCheckStates.get(signForm);
      if (state) state.pendingSubmit = null;
      setPending('email', false);
    });

    const submitFromMobileCta = (event) => {
      event.preventDefault();

      if (pending.size > 0 || !prepareSignature()) return;

      submitForm();
    };

    mobileSignCta?.addEventListener('click', submitFromMobileCta);
    mobileSignCta?.addEventListener('keydown', (event) => {
      if (![' ', 'Enter'].includes(event.key)) return;

      submitFromMobileCta(event);
    });
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

export const stepperTunnelClh = () => {
  const stepper = document.querySelector('.tunnel-clh-stepper');
  if (!stepper) return;

  const serverCount =
    stepper?.dataset.signedCount !== undefined ? parseInt(stepper?.dataset.signedCount, 10) : null;

  const localStorageIds = getSignedPetitionIds();
  const checkedCount = serverCount ?? localStorageIds.length;
  const limit = Math.min(checkedCount, stepper.children.length);

  Array.from(stepper.children).forEach((child) => child.classList.remove('is-checked'));

  Array.from(stepper.children)
    .slice(0, limit)
    .forEach((child) => child.classList.add('is-checked'));
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
