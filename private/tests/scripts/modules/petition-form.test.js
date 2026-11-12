import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
  clearSignatureStatusFromUrl,
  initTunnelClhForm,
  scrollToTunnelClhPetition,
  toggleFullFormPetition,
} from '../../../src/scripts/modules/petition-form';
import initTurnstileForms, { destroyTurnstileForms } from '../../../src/scripts/modules/turnstile';

// Rendered by the email input pattern (petition-form-fields.php).
const INVALID_EMAIL_MESSAGE = 'Adresse email invalide.';

const buildForm = (className, email) => {
  document.body.innerHTML = `
    <form class="${className}">
      <input class="email-input" type="email" name="user_email" value="${email}"
             data-invalid-message="${INVALID_EMAIL_MESSAGE}" required>
      <div class="full-form" style="display: none">
        <input class="firstname-input" name="user_firstname">
      </div>
    </form>
  `;

  return document.querySelector('.full-form');
};

const buildTunnelForm = (email) => buildForm('tunnel-clh-sign-form-anonymous', email);

const typeEmail = (emailInput, email) => {
  // eslint-disable-next-line no-param-reassign
  emailInput.value = email;
  emailInput.dispatchEvent(new Event('input'));
};

describe('toggleFullFormPetition', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal(
      'fetch',
      vi.fn(() =>
        Promise.resolve({
          ok: true,
          json: () => Promise.resolve({ exists: false }),
        }),
      ),
    );
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
    document.body.innerHTML = '';
  });

  it('checks an email already in the field when the page loads', async () => {
    const fullForm = buildTunnelForm('new.signer@example.org');

    toggleFullFormPetition();
    await vi.advanceTimersByTimeAsync(300);

    expect(fetch).toHaveBeenCalledOnce();
    expect(fetch.mock.calls[0][0]).toBe('/wp-json/humanity/v1/check-email');
    expect(fetch.mock.calls[0][1].body.get('email')).toBe('new.signer@example.org');
    expect(fullForm.style.display).toBe('flex');
  });

  it('does not check an empty email field when the page loads', async () => {
    buildTunnelForm('');

    toggleFullFormPetition();
    await vi.advanceTimersByTimeAsync(300);

    expect(fetch).not.toHaveBeenCalled();
  });

  it('does not check an email restored by the browser on the petition page', async () => {
    buildForm('signature-petition-form', 'visitor@example.org');

    toggleFullFormPetition();
    await vi.advanceTimersByTimeAsync(300);

    expect(fetch).not.toHaveBeenCalled();
  });

  it.each([
    ['petition page', 'signature-petition-form'],
    ['tunnel', 'tunnel-clh-sign-form-anonymous'],
  ])('blocks the submit of an invalid typed email on the %s', (_, className) => {
    buildForm(className, '');
    const emailInput = document.querySelector('.email-input');

    toggleFullFormPetition();
    typeEmail(emailInput, 'a@b');

    expect(emailInput.validity.customError).toBe(true);
    expect(emailInput.validationMessage).toBe(INVALID_EMAIL_MESSAGE);
  });

  it('blocks the submit of an invalid email restored by the browser on the petition page', () => {
    buildForm('signature-petition-form', 'a@b');
    const emailInput = document.querySelector('.email-input');

    toggleFullFormPetition();

    expect(emailInput.validationMessage).toBe(INVALID_EMAIL_MESSAGE);
  });

  it('lets the submit through once the typed email is valid', () => {
    buildTunnelForm('');
    const emailInput = document.querySelector('.email-input');

    toggleFullFormPetition();
    typeEmail(emailInput, 'a@b');
    typeEmail(emailInput, 'ada@example.org');

    expect(emailInput.validity.customError).toBe(false);
  });

  it('leaves an emptied email field to the native required message', () => {
    buildTunnelForm('');
    const emailInput = document.querySelector('.email-input');

    toggleFullFormPetition();
    typeEmail(emailInput, 'a@b');
    typeEmail(emailInput, '');

    expect(emailInput.validity.customError).toBe(false);
    expect(emailInput.validity.valueMissing).toBe(true);
  });
});

describe('CLH signature loading state', () => {
  let form;
  let button;
  let submissions;

  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal(
      'fetch',
      vi.fn(() => Promise.resolve({ ok: true, json: () => Promise.resolve({ exists: true }) })),
    );
    document.body.innerHTML = `
      <div class="page-tunnel-clh-card">
        <form id="clh-sign" class="tunnel-clh-sign-form-anonymous">
          <input class="email-input" type="email" name="user_email" required>
          <div class="full-form" style="display: none">
            <input class="firstname-input" name="user_firstname">
          </div>
        </form>
        <button type="submit" form="clh-sign" name="sign_petition"
          class="page-tunnel-clh-action-button is-primary">Signer la pétition</button>
        <a href="#clh-sign" data-sign-form="clh-sign">Signer</a>
      </div>
    `;
    form = document.querySelector('form');
    button = document.querySelector('button');
    submissions = [];
    toggleFullFormPetition();
    initTunnelClhForm();
    initTurnstileForms();
    // Observe accepted submissions, then prevent jsdom from navigating.
    form.addEventListener('submit', (event) => {
      if (!event.defaultPrevented) submissions.push(new FormData(form, event.submitter));
      event.preventDefault();
    });
  });

  afterEach(() => {
    window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true }));
    destroyTurnstileForms();
    vi.useRealTimers();
    vi.unstubAllGlobals();
    document.body.innerHTML = '';
  });

  it('leaves an invalid form idle', () => {
    button.click();
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(submissions).toHaveLength(0);
  });

  it('keeps the spinner through the POST, preserves the submitter and blocks duplicates', () => {
    form.elements.user_email.value = 'signer@example.org';
    button.click();
    expect(button.classList.contains('is-loading')).toBe(true);
    expect(button.disabled).toBe(false);
    expect(document.querySelector('[role="status"]').textContent).toContain('Traitement en cours');
    button.click();
    form.requestSubmit(button);
    document.querySelector('[data-sign-form]').click();
    expect(submissions).toHaveLength(1);
    expect(submissions[0].has('sign_petition')).toBe(true);
    window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true }));
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(button.getAttribute('aria-disabled')).toBe('false');
    button.click();
    expect(submissions).toHaveLength(2);
  });

  it('frees the button when the POST never completes', () => {
    form.elements.user_email.value = 'signer@example.org';
    button.click();
    vi.advanceTimersByTime(14999);
    expect(button.classList.contains('is-loading')).toBe(true);
    vi.advanceTimersByTime(1);
    expect(button.classList.contains('is-loading')).toBe(false);
    button.click();
    expect(submissions).toHaveLength(2);
  });

  it('waits for the email check, then sends only one signature', async () => {
    form.elements.user_email.value = 'signer@example.org';
    form.elements.user_email.dispatchEvent(new Event('input'));
    button.click();
    button.click();
    expect(button.classList.contains('is-loading')).toBe(true);
    expect(submissions).toHaveLength(0);
    await vi.advanceTimersByTimeAsync(300);
    expect(submissions).toHaveLength(1);
    expect(button.classList.contains('is-loading')).toBe(true);
  });

  it('stops waiting when the email check reveals required fields', async () => {
    fetch.mockResolvedValue({ ok: true, json: () => Promise.resolve({ exists: false }) });
    form.elements.user_email.value = 'new@example.org';
    form.elements.user_email.dispatchEvent(new Event('input'));
    button.click();
    await vi.advanceTimersByTimeAsync(300);
    expect(form.elements.user_firstname.required).toBe(true);
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(submissions).toHaveLength(0);
  });

  it('cancels the queued signature when the email is edited', async () => {
    form.elements.user_email.value = 'signer@example.org';
    form.elements.user_email.dispatchEvent(new Event('input'));
    button.click();
    form.elements.user_email.value = 'another@example.org';
    form.elements.user_email.dispatchEvent(new Event('input'));
    await vi.advanceTimersByTimeAsync(300);
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(submissions).toHaveLength(0);
  });

  it('covers Turnstile waiting and the subsequent submission', () => {
    form.insertAdjacentHTML('beforeend', '<div class="cf-turnstile"></div>');
    form.elements.user_email.value = 'signer@example.org';
    button.click();
    expect(button.classList.contains('is-loading')).toBe(true);
    expect(submissions).toHaveLength(0);
    form.insertAdjacentHTML('beforeend', '<input name="cf-turnstile-response" value="token">');
    window.aifTurnstileSuccess();
    expect(button.classList.contains('is-loading')).toBe(true);
    expect(submissions).toHaveLength(1);
    expect(submissions[0].has('sign_petition')).toBe(true);
  });

  it('restores the button after a Turnstile failure', () => {
    form.insertAdjacentHTML('beforeend', '<div class="cf-turnstile"></div>');
    form.elements.user_email.value = 'signer@example.org';
    button.click();
    window.aifTurnstileFailure();
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(button.disabled).toBe(false);
    expect(button.getAttribute('aria-disabled')).toBe('false');
    expect(submissions).toHaveLength(0);
  });

  it('clears a Turnstile wait restored from history, without replaying the signature', async () => {
    form.insertAdjacentHTML('beforeend', '<div class="cf-turnstile"></div>');
    form.elements.user_email.value = 'signer@example.org';
    button.click();
    window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: true }));
    form.insertAdjacentHTML('beforeend', '<input name="cf-turnstile-response" value="token">');
    await vi.advanceTimersByTimeAsync(500);
    expect(button.classList.contains('is-loading')).toBe(false);
    expect(button.disabled).toBe(false);
    expect(submissions).toHaveLength(0);
  });

  it('does not cancel a queued signature when the initial page finishes loading', async () => {
    form.elements.user_email.value = 'signer@example.org';
    form.elements.user_email.dispatchEvent(new Event('input'));
    button.click();
    window.dispatchEvent(new PageTransitionEvent('pageshow', { persisted: false }));
    await vi.advanceTimersByTimeAsync(300);
    expect(submissions).toHaveLength(1);
    expect(button.classList.contains('is-loading')).toBe(true);
  });
});

describe('scrollToTunnelClhPetition', () => {
  let scrollIntoView;

  beforeEach(() => {
    document.body.innerHTML = '<input id="email"><div id="tunnel-clh-petition"></div>';
    scrollIntoView = vi.fn();
    document.getElementById('tunnel-clh-petition').scrollIntoView = scrollIntoView;
    vi.stubGlobal('matchMedia', vi.fn(() => ({ matches: true })));
  });

  afterEach(() => {
    vi.unstubAllGlobals();
    window.scrollY = 0;
    document.body.innerHTML = '';
  });

  it('scrolls to the petition on mobile', () => {
    scrollToTunnelClhPetition();
    expect(scrollIntoView).toHaveBeenCalledOnce();
  });

  it('leaves the visitor in place once they scrolled', () => {
    window.scrollY = 120;
    scrollToTunnelClhPetition();
    expect(scrollIntoView).not.toHaveBeenCalled();
  });

  it('leaves the visitor in place while they type', () => {
    document.getElementById('email').focus();
    scrollToTunnelClhPetition();
    expect(scrollIntoView).not.toHaveBeenCalled();
  });
});

describe('clearSignatureStatusFromUrl', () => {
  const visit = (url) => window.history.replaceState(null, '', url);
  const currentUrl = () =>
    `${window.location.pathname}${window.location.search}${window.location.hash}`;

  afterEach(() => {
    visit('/');
    document.body.innerHTML = '';
  });

  it('removes the status of a rejected signature from the petition page address', () => {
    document.body.innerHTML = '<form class="signature-petition-form"></form>';
    visit(
      '/petitions/une-petition/?utm_source=mail&signature_status=error&reserved_originecode=ABC#petition',
    );

    clearSignatureStatusFromUrl();

    expect(currentUrl()).toBe(
      '/petitions/une-petition/?utm_source=mail&reserved_originecode=ABC#petition',
    );
  });

  it('leaves the address of a page without the petition form unchanged', () => {
    document.body.innerHTML = '<form class="tunnel-clh-sign-form-anonymous"></form>';
    visit('/campagne/?signature_status=invalid');

    clearSignatureStatusFromUrl();

    expect(currentUrl()).toBe('/campagne/?signature_status=invalid');
  });

  it('does not rewrite an address without a signature status', () => {
    document.body.innerHTML = '<form class="signature-petition-form"></form>';
    visit('/petitions/une-petition/?utm_source=mail');
    const replaceState = vi.spyOn(window.history, 'replaceState');

    clearSignatureStatusFromUrl();

    expect(replaceState).not.toHaveBeenCalled();
    replaceState.mockRestore();
  });
});
