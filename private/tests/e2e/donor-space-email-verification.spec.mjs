import { expect, test, uniqueEmail } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

// Seeded by support/seed-wordpress.php; the member lookup for these addresses
// is faked by aif-e2e-support.php.
const UNVERIFIED_DONOR = { email: 'e2e-unverified-donor@example.test', password: 'E2e-Unverified-1!' };
const VERIFIED_DONOR = { email: 'e2e-verified-donor@example.test', password: 'E2e-Verified-1!' };
const LEGACY_EDITOR_DONOR = { email: 'e2e-legacy-editor-donor@example.test', password: 'E2e-Legacy-Editor-1!' };
const LEGACY_SUBSCRIBER = { email: 'e2e-legacy-subscriber@example.test', password: 'E2e-Legacy-Subscriber-1!' };

const LOGIN_FORM = '.login-form form.aif-form-container';

test.describe('donor space e-mail verification', () => {
  [
    ['plus', 'donor+tag'],
    ['apostrophe', "o'connor"],
    ['plus and apostrophe', "o'connor+tag"],
    ['exclamation mark', 'donor!tag'],
    ['dollar', 'donor$tag'],
    ['ampersand', 'donor&tag'],
    ['asterisk', 'donor*tag', 404],
    ['equals sign', 'donor=tag'],
    ['combined path sub-delimiters', 'donor!$&=tag'],
  ].forEach(([character, localPart, providerStatus = 200]) => {
    const outcome = providerStatus === 200 ? 'starts account verification' : 'reports a provider error';
    test(`${outcome} for a donor address with ${character}`, async ({
      page,
      request,
      gotoWithoutCookieOverlay,
      salesforceTestId,
    }) => {
      const email = uniqueEmail().replace('e2e-', () => `e2e-${localPart}-`);
      await resetSalesforceCalls(request, salesforceTestId);
      await mockSuccessfulTurnstile(page, { token: 'mock-special-character-token' });

      await gotoWithoutCookieOverlay('/creer-votre-compte/');
      const signup = 'form:has(#email)';
      await setServerSideTurnstileResult(page, signup, { success: true });
      await page.locator(`${signup} #email`).fill(email);
      await page.locator(`${signup} button[type="submit"]`).click();

      if (providerStatus === 404) {
        await expect(page).toHaveURL(/\/creer-votre-compte\//);
        await expect(page.getByText('Votre espace est temporairement indisponible.')).toBeVisible();
        await expect(page.getByText('L’adresse email renseignée ne trouve pas de correspondance dans notre système.')).toHaveCount(0);
      } else {
        await expect(page).toHaveURL(/\/verifier-votre-email\//);
        await expect(page.getByRole('heading', { name: 'Validation de la création de mon compte' })).toBeVisible();
        expect(new URL(page.url()).searchParams.get('user')).toBe(email);
      }

      const calls = await getSalesforceCalls(request, salesforceTestId);
      const searches = calls.filter(({ url }) => url.includes('/apexrest/search/v1/'));
      expect(searches).toHaveLength(1);
      expect(searches[0].method).toBe('GET');
      expect(new URL(searches[0].url).pathname).toBe(`/services/apexrest/search/v1/${email}`);
      expect(new URL(searches[0].url).pathname).toContain(localPart);
      if (providerStatus === 404) {
        expect(calls.filter(({ url }) => url.includes('/sobjects/Contact/'))).toHaveLength(0);
      }
    });
  });

  ['absent', 'ineligible'].forEach((result) => {
    test(`treats an Apex ${result} contact as a business refusal across account forms`, async ({
      page,
      gotoWithoutCookieOverlay,
      salesforceTestId,
    }) => {
      await page.setExtraHTTPHeaders({
        'X-AIF-E2E-Test-Id': salesforceTestId,
        'X-AIF-E2E-Apex-Result': result,
      });
      await mockSuccessfulTurnstile(page, { token: 'mock-apex-result-token' });

      await gotoWithoutCookieOverlay('/creer-votre-compte/');
      const signup = 'form:has(#email)';
      await setServerSideTurnstileResult(page, signup, { success: true });
      await page.locator(`${signup} #email`).fill(VERIFIED_DONOR.email);
      await page.locator(`${signup} button[type="submit"]`).click();
      // Same outcome as a successful signup: the page does not tell whether the address has access.
      await expect(page).toHaveURL(/\/verifier-votre-email\//);
      await expect(page.getByText('L’adresse email renseignée ne trouve pas de correspondance dans notre système.')).toHaveCount(0);
      await expect(page.getByText('Votre espace est temporairement indisponible.')).toHaveCount(0);

      await gotoWithoutCookieOverlay('/connectez-vous/');
      await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
      await page.locator(`${LOGIN_FORM} #email`).fill(VERIFIED_DONOR.email);
      await page.locator(`${LOGIN_FORM} #password`).fill(VERIFIED_DONOR.password);
      await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();
      await expect(page.getByText("L'adresse email renseignée ne trouve pas de correspondance dans notre système")).toBeVisible();

      await gotoWithoutCookieOverlay(`/verifier-votre-email/?user=${encodeURIComponent(VERIFIED_DONOR.email)}`);
      const resend = 'form:has(input[value="2FA_send_code"])';
      await setServerSideTurnstileResult(page, resend, { success: true });
      await page.locator(`${resend} button[type="submit"]`).click();
      await expect(page.getByText(/Si votre adresse correspond à un compte, un code arrivera/)).toBeVisible();
      await expect(page.getByText('Utilisateur inconnu', { exact: true })).toHaveCount(0);
      await expect(page.getByText('Votre espace est temporairement indisponible.')).toHaveCount(0);
    });
  });

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

  test('lets a historically verified subscriber log in with the existing password', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-legacy-subscriber-token' });
    await gotoWithoutCookieOverlay('/connectez-vous/');
    await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
    await page.locator(`${LOGIN_FORM} #email`).fill(LEGACY_SUBSCRIBER.email);
    await page.locator(`${LOGIN_FORM} #password`).fill(LEGACY_SUBSCRIBER.password);
    await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();

    await expect(page).toHaveURL(/\/mon-espace\/$/);
    const response = await page.reload();
    expect(response.status()).toBe(200);
    await expect(page).toHaveURL(/\/mon-espace\/$/);
  });

  test('lets a historically verified subscriber use the native login', async ({ page }) => {
    await page.goto('/wp-login.php?redirect_to=%2Fmon-espace%2F');
    await expect(page.locator('#user_login')).toBeFocused();
    await page.locator('#user_login').fill(LEGACY_SUBSCRIBER.email);
    await page.locator('#user_pass').fill(LEGACY_SUBSCRIBER.password);
    await page.locator('#wp-submit').click();

    await expect(page).toHaveURL(/\/mon-espace\/$/);
    const response = await page.reload();
    expect(response.status()).toBe(200);
  });

  test('refuses an unverified subscriber on wp-login.php and keeps the donor space closed', async ({
    page,
  }) => {
    await page.goto('/wp-login.php');
    // WordPress focuses this field on a timer; wait before filling the password.
    await expect(page.locator('#user_login')).toBeFocused();
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
