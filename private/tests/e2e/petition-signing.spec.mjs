import { expect, test } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';

const PETITION_PATH = '/petitions/aif-e2e-petition/';
const PETITION_FORM = 'form.signature-petition-form';

const fillSignatureForm = async (page, email) => {
  await page.locator('input[name="user_email"]').fill(email);
  // The civility radio inputs are visually hidden by custom styling; the
  // associated <label> is the actual clickable/visible control.
  await page.getByText('Mme', { exact: true }).click();
  await page.locator('input[name="user_firstname"]').fill('Ada');
  await page.locator('input[name="user_lastname"]').fill('Lovelace');
  await page.locator('input[name="user_zipcode"]').fill('75001');
  await page.locator('input[name="user_phone"]').fill('0102030405');
};

test.describe('petition signing', () => {
  test('signs the petition and lands on the thank-you page when Turnstile succeeds', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-petition-signature-token' });

    await gotoWithoutCookieOverlay(PETITION_PATH);
    await setServerSideTurnstileResult(page, PETITION_FORM, { success: true });

    const uniqueEmail = `e2e-${Date.now()}-${Math.floor(Math.random() * 1e6)}@example.test`;
    await fillSignatureForm(page, uniqueEmail);

    await page.getByRole('button', { name: 'Signer' }).click();

    await expect(page).toHaveURL(/\/petitions\/aif-e2e-petition\/merci\/?/);
    await expect(page.getByText('Vous avez signé la pétition')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Justice pour toustes (e2e)' })).toBeVisible();
  });

  test('shows an inline error and does not sign when Turnstile verification fails', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-petition-signature-rejected-token' });

    await gotoWithoutCookieOverlay(PETITION_PATH);
    await setServerSideTurnstileResult(page, PETITION_FORM, {
      success: false,
      error: 'invalid-input-response',
    });

    const uniqueEmail = `e2e-${Date.now()}-${Math.floor(Math.random() * 1e6)}@example.test`;
    await fillSignatureForm(page, uniqueEmail);

    await page.getByRole('button', { name: 'Signer' }).click();

    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect(page).toHaveURL(new RegExp(`${PETITION_PATH.replace(/\//g, '\\/')  }$`));
  });
});
