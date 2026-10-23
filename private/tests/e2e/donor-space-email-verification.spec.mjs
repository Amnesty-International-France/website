import { expect, test, uniqueEmail } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';

// Seeded by support/seed-wordpress.php; the member lookup for these addresses
// is faked by aif-e2e-support.php.
const UNVERIFIED_DONOR = { email: 'e2e-unverified-donor@example.test', password: 'E2e-Unverified-1!' };
const VERIFIED_DONOR = { email: 'e2e-verified-donor@example.test', password: 'E2e-Verified-1!' };
const LEGACY_EDITOR_DONOR = { email: 'e2e-legacy-editor-donor@example.test', password: 'E2e-Legacy-Editor-1!' };
const LEGACY_SUBSCRIBER = { email: 'e2e-legacy-subscriber@example.test', password: 'E2e-Legacy-Subscriber-1!' };

const LOGIN_FORM = '.login-form form.aif-form-container';

test.describe('donor space e-mail verification', () => {
  test('keeps a native password-reset link usable for an address with an apostrophe', async ({
    page,
    request,
  }, testInfo) => {
    const response = await request.post('/wp-json/aif-e2e/v1/native-password-reset', {
      data: { suffix: testInfo.project.name },
    });
    expect(response.ok()).toBe(true);
    const { url } = await response.json();

    await page.goto(url);

    await expect(page.locator('#resetpassform #pass1')).toBeVisible();
  });

  test('lets a legacy non-subscriber donor log in and reach the donor space', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-legacy-donor-token' });

    await gotoWithoutCookieOverlay('/connectez-vous/');
    await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
    await page.locator(`${LOGIN_FORM} #email`).fill(LEGACY_EDITOR_DONOR.email);
    await page.locator(`${LOGIN_FORM} #password`).fill(LEGACY_EDITOR_DONOR.password);
    await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();

    await expect(page).toHaveURL(/\/mon-espace\/$/);
  });

  test('still requires verification for a subscriber with the legacy flag', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-legacy-subscriber-token' });
    await gotoWithoutCookieOverlay('/connectez-vous/');
    await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
    await page.locator(`${LOGIN_FORM} #email`).fill(LEGACY_SUBSCRIBER.email);
    await page.locator(`${LOGIN_FORM} #password`).fill(LEGACY_SUBSCRIBER.password);
    await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();

    await expect(page.getByText('Votre email ne semble pas encore vérifié')).toBeVisible();
    await page.goto('/mon-espace/');
    await expect(page).toHaveURL(/\/connectez-vous\//);
  });

  test('refuses an unverified subscriber on wp-login.php and keeps the donor space closed', async ({
    page,
  }) => {
    await page.goto('/wp-login.php');
    await page.locator('#user_login').fill(UNVERIFIED_DONOR.email);
    await page.locator('#user_pass').fill(UNVERIFIED_DONOR.password);
    await page.locator('#wp-submit').click();

    await expect(page.locator('#login_error')).toContainText('Votre adresse e-mail n’est pas encore vérifiée.');

    // No session was opened above, so this only checks the guard for visitors;
    // session revocation is covered by the e-mail change test below.
    await page.goto('/mon-espace/');
    await expect(page).toHaveURL(/\/connectez-vous\//);
  });

  test('requires a new verification after an e-mail change', async ({
    page,
    request,
    gotoWithoutCookieOverlay,
  }, testInfo) => {
    const donorResponse = await request.post('/wp-json/aif-e2e/v1/verified-donor', {
      data: { suffix: testInfo.project.name },
    });
    expect(donorResponse.ok()).toBe(true);
    const donor = await donorResponse.json();

    await mockSuccessfulTurnstile(page, { token: 'mock-donor-login-token' });
    await gotoWithoutCookieOverlay('/connectez-vous/');
    await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
    await page.locator(`${LOGIN_FORM} #email`).fill(donor.email);
    await page.locator(`${LOGIN_FORM} #password`).fill(donor.password);
    await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();
    await expect(page).toHaveURL(/\/mon-espace\/$/);

    const status = await page.evaluate(async (email) => {
      const nonce = await (await fetch('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
      const response = await fetch('/wp-json/wp/v2/users/me', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify({ email }),
      });

      return response.status;
    }, uniqueEmail());
    expect(status).toBe(200);

    await page.goto('/mon-espace/');
    await expect(page).toHaveURL(/\/connectez-vous\//);

    await page.goto('/wp-admin/profile.php');
    await expect(page).toHaveURL(/\/wp-login\.php/);
  });

  test('lets a verified donor log in and reach the donor space', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-donor-login-token' });

    await gotoWithoutCookieOverlay('/connectez-vous/');
    await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
    await page.locator(`${LOGIN_FORM} #email`).fill(VERIFIED_DONOR.email);
    await page.locator(`${LOGIN_FORM} #password`).fill(VERIFIED_DONOR.password);
    await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();

    await expect(page).toHaveURL(/\/mon-espace\/$/);
  });
});
