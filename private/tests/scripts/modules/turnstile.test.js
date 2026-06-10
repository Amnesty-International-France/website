import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import initTurnstileForms, { destroyTurnstileForms } from '../../../src/scripts/modules/turnstile';

const dispatchSubmit = (form, submitter = undefined) => {
  const event = new Event('submit', { bubbles: true, cancelable: true });

  if (submitter) {
    Object.defineProperty(event, 'submitter', { value: submitter });
  }

  form.dispatchEvent(event);

  return event;
};

const addResponse = (form, value = 'token') => {
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'cf-turnstile-response';
  input.value = value;
  form.appendChild(input);

  return input;
};

const buildForm = ({ className = '', submitterName = 'sign_petition' } = {}) => {
  document.body.innerHTML = `
    <form class="${className}">
      <div class="cf-turnstile"></div>
      <input name="email" value="user@example.org">
      <button type="submit" name="${submitterName}" value="1">Submit</button>
    </form>
  `;

  const form = document.querySelector('form');
  const button = form.querySelector('button');
  form.requestSubmit = vi.fn();

  return { form, button };
};

describe('turnstile form guard', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    document.body.innerHTML = '';
    initTurnstileForms();
  });

  afterEach(() => {
    destroyTurnstileForms();
    vi.useRealTimers();
    vi.restoreAllMocks();
  });

  it('waits for a Turnstile response before submitting and ignores stale polling ticks', () => {
    const { form, button } = buildForm();

    const event = dispatchSubmit(form, button);

    expect(event.defaultPrevented).toBe(true);
    expect(button.disabled).toBe(true);
    expect(form.querySelector('[data-turnstile-client-error]')?.textContent).toContain(
      'vérification de sécurité est en cours',
    );

    addResponse(form);
    window.aifTurnstileSuccess();
    vi.advanceTimersByTime(3000);

    expect(form.requestSubmit).toHaveBeenCalledTimes(1);
    expect(form.requestSubmit).toHaveBeenCalledWith(button);
    expect(button.disabled).toBe(false);
  });

  it('falls back to the form submit button for keyboard submits without SubmitEvent.submitter', () => {
    const { form, button } = buildForm({ submitterName: 'sign_newsletter' });

    dispatchSubmit(form);
    addResponse(form);
    window.aifTurnstileSuccess();

    expect(form.requestSubmit).toHaveBeenCalledWith(button);
  });

  it('does not use or re-enable an already disabled fallback submit button', () => {
    const { form, button } = buildForm({ submitterName: 'sign_lead' });
    button.disabled = true;

    dispatchSubmit(form);
    addResponse(form);
    window.aifTurnstileSuccess();

    expect(form.requestSubmit).toHaveBeenCalledWith(undefined);
    expect(button.disabled).toBe(true);
  });

  it('preserves the named submitter when requestSubmit is unavailable', () => {
    const { form } = buildForm({ submitterName: 'sign_urgent_action' });
    const nativeSubmit = vi.fn();
    Object.defineProperty(form, 'requestSubmit', { value: undefined, configurable: true });
    Object.defineProperty(form, 'submit', { value: nativeSubmit, configurable: true });

    dispatchSubmit(form);
    addResponse(form);
    window.aifTurnstileSuccess();

    expect(nativeSubmit).toHaveBeenCalledTimes(1);
    expect(form.querySelector('input[type="hidden"][name="sign_urgent_action"]')).not.toBeNull();
  });

  it('renders footer newsletter errors outside the flex form row', () => {
    document.body.innerHTML = `
      <div class="nl-container">
        <form class="newsletter-lead-form">
          <div class="cf-turnstile"></div>
          <input name="newsletter-lead" value="user@example.org">
          <button type="submit" name="sign_lead" value="1">OK</button>
        </form>
      </div>
    `;

    dispatchSubmit(document.querySelector('form'));

    const containerError = document.querySelector('.nl-container > [data-turnstile-client-error]');

    expect(containerError).not.toBeNull();
    expect(document.querySelector('form > [data-turnstile-client-error]')).toBeNull();
  });
});
