import { expect, test } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';

const FORGOT_PASSWORD_PATH = '/mot-de-passe-oublie/';
const FORGOT_PASSWORD_FORM = 'form.aif-form-container';

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
    await setServerSideTurnstileResult(page, FORGOT_PASSWORD_FORM, { success: true });
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
    await setServerSideTurnstileResult(page, FORGOT_PASSWORD_FORM, {
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
