import { expect, test, uniqueEmail } from './support/fixtures';
import { setupBusinessFormFixtures } from './support/business-forms';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';

const ANONYMOUS_FORM = 'form.tunnel-clh-sign-form-anonymous';

test.describe('CLH tunnel signature rejected by the server', () => {
  let fixtures;

  test.beforeEach(async ({ request, salesforceTestId }) => {
    fixtures = await setupBusinessFormFixtures(request, salesforceTestId);
    expect(fixtures.ready).toBe(true);
  });

  test('shows the error above the same petition with the typed fields, once', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    const email = uniqueEmail();
    await mockSuccessfulTurnstile(page, { token: 'mock-clh-sign-rejected-token' });
    await gotoWithoutCookieOverlay(fixtures.tunnel_path);

    const form = page.locator(ANONYMOUS_FORM);
    await expect(form).toHaveCount(1);
    const formId = await form.getAttribute('id');

    await form.locator('input[name="user_email"]').fill(email);
    // The rest of the form appears once /check-email reports an unknown address.
    await expect(form.locator('input[name="user_firstname"]')).toBeVisible();
    // The civility radio inputs are visually hidden; their labels are the visible controls.
    await form.getByText('Mme', { exact: true }).click();
    await form.locator('input[name="user_firstname"]').fill('Ada');
    await form.locator('input[name="user_lastname"]').fill('Lovelace');
    await form.locator('input[name="user_zipcode"]').fill('75001');
    await form.locator('input[name="user_phone"]').fill('0102030405');

    await setServerSideTurnstileResult(page, ANONYMOUS_FORM, { success: false });
    await page.locator(`[form="${formId}"][name="sign_petition"]:visible`).first().click();

    await expect(page).toHaveURL(/signature_status=turnstile/);
    expect(new URL(page.url()).searchParams.get('clh_petition')).toBe(String(fixtures.petition_id));
    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect(form.locator('input[name="petition_id"]')).toHaveValue(
      String(fixtures.petition_id),
    );
    await expect(form.locator('input[name="user_email"]')).toHaveValue(email);
    // The kept email is checked again on load, which shows the kept identity.
    await expect(form.locator('input[name="user_firstname"]')).toBeVisible();
    await expect(form.locator('input[name="user_firstname"]')).toHaveValue('Ada');
    await expect(form.locator('input[name="user_lastname"]')).toHaveValue('Lovelace');
    await expect(form.locator('input[name="user_zipcode"]')).toHaveValue('75001');
    await expect(form.locator('input[name="user_phone"]')).toHaveValue('0102030405');
    await expect(form.locator('input[name="civility"][value="Mme"]')).toBeChecked();

    await page.reload();

    await expect(form.locator('input[name="user_email"]')).toHaveValue('');
  });
});
