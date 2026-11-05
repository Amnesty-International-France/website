import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { toggleFullFormPetition } from '../../../src/scripts/modules/petition-form';

const buildForm = (className, email) => {
  document.body.innerHTML = `
    <form class="${className}">
      <input class="email-input" type="email" name="user_email" value="${email}">
      <div class="full-form" style="display: none">
        <input class="firstname-input" name="user_firstname">
      </div>
    </form>
  `;

  return document.querySelector('.full-form');
};

const buildTunnelForm = (email) => buildForm('tunnel-clh-sign-form-anonymous', email);

describe('toggleFullFormPetition', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal(
      'fetch',
      vi.fn(() => Promise.resolve({ ok: true, json: () => Promise.resolve({ exists: false }) })),
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
});
