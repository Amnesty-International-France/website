import { expect, test } from './support/fixtures.mjs';
import { mockSuccessfulTurnstile } from './support/turnstile.mjs';

const FORGOT_PASSWORD_PATH = '/mot-de-passe-oublie/';

const setServerSideTurnstileResult = async (
  page,
  { success, error = 'invalid-input-response' },
) => {
  await page.locator('form.aif-form-container').evaluate(
    (form, options) => {
      const appendHiddenInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      };

      appendHiddenInput('aif_e2e_turnstile_verify_success', options.success ? '1' : '0');

      if (!options.success) {
        appendHiddenInput('aif_e2e_turnstile_verify_error', options.error);
      }
    },
    { success, error },
  );
};

const submitForgotPasswordForm = async (page, email = 'unknown@example.org') => {
  await page.getByLabel('Votre email (obligatoire) :').fill(email);
  await page.locator('#submit-btn').click();
};

test.describe('forgot password Turnstile server-side flow', () => {
  test('continues to the business response when Turnstile verification succeeds', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-server-accepted-token' });

    await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);
    await setServerSideTurnstileResult(page, { success: true });
    await submitForgotPasswordForm(page);

    await expect(page.getByText('Votre utilisateur nous est inconnu')).toBeVisible();
    await expect(page.getByText('La vérification de sécurité a échoué.')).toHaveCount(0);
  });

  test('stops on the Turnstile error when server-side verification fails', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-server-rejected-token' });

    await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);
    await setServerSideTurnstileResult(page, {
      success: false,
      error: 'invalid-input-response',
    });
    await submitForgotPasswordForm(page);

    await expect(
      page.getByText('La vérification de sécurité a échoué.', { exact: true }),
    ).toBeVisible();
    await expect(page.getByText('Votre utilisateur nous est inconnu')).toHaveCount(0);
  });
});
