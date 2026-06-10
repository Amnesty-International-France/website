const VERIFYING_MESSAGE =
  'La vérification de sécurité est en cours. Le formulaire va être envoyé automatiquement.';
const FAILURE_MESSAGE =
  'La vérification de sécurité n’a pas pu être effectuée. Veuillez recharger la page puis réessayer.';

/**
 * @typedef {HTMLButtonElement|HTMLInputElement} Submitter
 */

/**
 * Checks whether an element is a submit button/input supported by the guard.
 *
 * @param {unknown} element Element to check.
 * @returns {element is Submitter} Whether the element can submit a form.
 */
const isSubmitter = (element) =>
  element instanceof HTMLButtonElement || element instanceof HTMLInputElement;

/** @type {Set<HTMLFormElement>} */
const pendingForms = new Set();
/** @type {WeakMap<HTMLFormElement, Submitter>} */
let submitters = new WeakMap();
let initialized = false;

const SUBMITTER_SELECTOR =
  'button[type="submit"]:not(:disabled), input[type="submit"]:not(:disabled), button:not([type]):not(:disabled)';

/**
 * Returns the Turnstile widget container attached to a protected form.
 *
 * @param {HTMLFormElement} form Form that may contain a Turnstile widget.
 * @returns {Element|null} Turnstile widget element, if present.
 */
const getTurnstile = (form) => form.querySelector('.cf-turnstile');

/**
 * Reads the hidden token field created by Cloudflare Turnstile.
 *
 * @param {HTMLFormElement} form Form protected by Turnstile.
 * @returns {string} Current Turnstile response token or an empty string.
 */
const getResponse = (form) => {
  const response = form.querySelector('[name="cf-turnstile-response"]');

  return response instanceof HTMLInputElement ? response.value : '';
};

/**
 * Finds the submitter that should be preserved when the form is resubmitted.
 *
 * @param {HTMLFormElement} form Submitted form.
 * @param {SubmitEvent|Event} event Original submit event.
 * @returns {Submitter|null} Submit button/input to pass to requestSubmit.
 */
const getSubmitter = (form, event) => {
  const eventSubmitter = 'submitter' in event ? event.submitter : null;

  if (isSubmitter(eventSubmitter)) {
    return eventSubmitter;
  }

  const rememberedSubmitter = submitters.get(form);
  if (rememberedSubmitter) {
    return rememberedSubmitter;
  }

  const fallbackSubmitter = form.querySelector(SUBMITTER_SELECTOR);

  return isSubmitter(fallbackSubmitter) ? fallbackSubmitter : null;
};

/**
 * Chooses where client-side Turnstile messages should be inserted.
 *
 * @param {HTMLFormElement} form Form showing the message.
 * @param {Element|null} widget Turnstile widget inside the form.
 * @returns {Element} Element that should contain the message.
 */
const getMessageTarget = (form, widget) => {
  if (form.classList.contains('newsletter-lead-form')) {
    return form.parentElement || form;
  }

  return widget || form;
};

/**
 * Displays or updates a client-side Turnstile message for a form.
 *
 * @param {HTMLFormElement} form Form associated with the message.
 * @param {string} message Message to display.
 * @returns {void}
 */
const showMessage = (form, message) => {
  const widget = getTurnstile(form);
  const target = getMessageTarget(form, widget);
  let error = form.querySelector('[data-turnstile-client-error]');

  if (!error && target !== form) {
    error = target.querySelector('[data-turnstile-client-error]');
  }

  if (!error) {
    error = document.createElement('div');
    error.setAttribute('data-turnstile-client-error', 'true');
    error.setAttribute('role', 'alert');
    error.className = 'form-mess error';

    if (target === form) {
      form.prepend(error);
    } else if (target === widget) {
      widget.insertAdjacentElement('afterend', error);
    } else {
      target.appendChild(error);
    }
  }

  error.textContent = message;
  error.classList.remove('hidden');
};

/**
 * Hides the client-side Turnstile message for a form.
 *
 * @param {HTMLFormElement} form Form associated with the message.
 * @returns {void}
 */
const hideMessage = (form) => {
  const widget = getTurnstile(form);
  const target = getMessageTarget(form, widget);
  const error =
    form.querySelector('[data-turnstile-client-error]') ||
    target.querySelector('[data-turnstile-client-error]');

  if (!error) return;

  error.classList.add('hidden');
  error.textContent = '';
};

/**
 * Toggles submitter busy state while waiting for the Turnstile token.
 *
 * @param {Submitter|null} submitter Submit button/input to toggle.
 * @param {boolean} isSubmitting Whether the form is waiting for verification.
 * @returns {void}
 */
const setSubmitting = (submitter, isSubmitting) => {
  if (!submitter) return;

  const button = submitter;
  button.disabled = isSubmitting;
  button.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
};

/**
 * Resubmits a pending form after Turnstile has produced a response token.
 *
 * @param {HTMLFormElement} form Pending form to resubmit.
 * @param {Submitter|null} submitter Submitter to preserve if available.
 * @returns {void}
 */
const resubmit = (form, submitter) => {
  if (!pendingForms.has(form)) return;

  pendingForms.delete(form);
  setSubmitting(submitter, false);
  hideMessage(form);

  if (typeof form.requestSubmit === 'function') {
    form.requestSubmit(submitter || undefined);
    return;
  }

  if (submitter?.name) {
    const hiddenSubmitter = document.createElement('input');
    hiddenSubmitter.type = 'hidden';
    hiddenSubmitter.name = submitter.name;
    hiddenSubmitter.value = submitter.value || '';
    form.appendChild(hiddenSubmitter);
  }

  form.submit();
};

/**
 * Polls briefly for Cloudflare's hidden response input after a user submits early.
 *
 * @param {HTMLFormElement} form Pending form.
 * @param {Submitter|null} submitter Submitter to preserve on resubmit.
 * @param {number} attempts Remaining polling attempts.
 * @returns {void}
 */
const waitForResponse = (form, submitter, attempts) => {
  if (!pendingForms.has(form)) return;

  if (getResponse(form)) {
    resubmit(form, submitter);
    return;
  }

  if (attempts <= 0) {
    pendingForms.delete(form);
    setSubmitting(submitter, false);
    showMessage(form, FAILURE_MESSAGE);
    return;
  }

  window.setTimeout(() => {
    waitForResponse(form, submitter, attempts - 1);
  }, 250);
};

/**
 * Prevents protected forms from posting until Turnstile has created a token.
 *
 * @param {SubmitEvent|Event} event Captured submit event.
 * @returns {void}
 */
const submitWhenVerified = (event) => {
  const form = event.target;

  if (!(form instanceof HTMLFormElement) || !getTurnstile(form) || getResponse(form)) {
    return;
  }

  event.preventDefault();

  if (pendingForms.has(form)) return;

  const submitter = getSubmitter(form, event);
  if (submitter) {
    submitters.set(form, submitter);
  }

  pendingForms.add(form);
  setSubmitting(submitter, true);
  showMessage(form, VERIFYING_MESSAGE);
  waitForResponse(form, submitter, 12);
};

/**
 * Remembers clicked submitters so named buttons survive delayed resubmission.
 *
 * @param {MouseEvent} event Captured click event.
 * @returns {void}
 */
const rememberSubmitter = (event) => {
  if (!(event.target instanceof Element)) return;

  const submitter = event.target.closest(SUBMITTER_SELECTOR);

  if (isSubmitter(submitter) && submitter.form) {
    submitters.set(submitter.form, submitter);
  }
};

/**
 * Handles Cloudflare Turnstile success callbacks for all rendered widgets.
 *
 * @returns {void}
 */
const handleSuccess = () => {
  document.querySelectorAll('form').forEach((form) => {
    if (!getTurnstile(form) || !getResponse(form)) return;

    if (pendingForms.has(form)) {
      resubmit(form, submitters.get(form) || null);
      return;
    }

    hideMessage(form);
  });
};

/**
 * Handles Cloudflare Turnstile error, expired, timeout, and unsupported callbacks.
 *
 * @returns {void}
 */
const handleFailure = () => {
  pendingForms.forEach((form) => {
    if (getResponse(form)) return;

    pendingForms.delete(form);
    setSubmitting(submitters.get(form) || null, false);
    showMessage(form, FAILURE_MESSAGE);
  });
};

Object.assign(window, {
  aifTurnstileSuccess: handleSuccess,
  aifTurnstileFailure: handleFailure,
});

/**
 * Installs global listeners used to guard Turnstile-protected forms.
 *
 * @returns {void}
 */
const initTurnstileForms = () => {
  if (initialized) return;

  initialized = true;
  document.addEventListener('click', rememberSubmitter, true);
  document.addEventListener('submit', submitWhenVerified, true);
};

/**
 * Removes Turnstile form guard listeners and clears module state.
 *
 * @returns {void}
 */
export const destroyTurnstileForms = () => {
  if (!initialized) return;

  initialized = false;
  pendingForms.clear();
  submitters = new WeakMap();
  document.removeEventListener('click', rememberSubmitter, true);
  document.removeEventListener('submit', submitWhenVerified, true);
};

export default initTurnstileForms;
