import { expect, test } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

const NEWSLETTER_PATH = '/newsletter/';
const NEWSLETTER_FORM = '#newsletter-form';

// Unlike petition signing / legs / foundation forms (verified to only touch
// local DB, or a Jetpack flow with no Salesforce wiring at all), newsletter
// signup (patterns/page-nl-content.php) makes a REAL, synchronous Salesforce
// call as part of handling the POST: for a brand new email it calls
// insert_user() (local) then post_salesforce_users() (Contact creation).
// aif-e2e-support.php mocks every Salesforce call and records it, so this
// spec can assert that call actually happened, not just that the page
// behaved as if it had.

test.describe('newsletter signup', () => {
  test.beforeEach(async ({ request, salesforceTestId }) => {
    await resetSalesforceCalls(request, salesforceTestId);
  });

  test('signs up a new subscriber and triggers a real Salesforce Contact creation call', async ({
    page,
    request,
    gotoWithoutCookieOverlay,
    salesforceTestId,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-newsletter-signup-token' });

    await gotoWithoutCookieOverlay(NEWSLETTER_PATH);
    await setServerSideTurnstileResult(page, NEWSLETTER_FORM, { success: true });

    const uniqueEmail = `e2e-${Date.now()}-${Math.floor(Math.random() * 1e6)}@example.test`;
    await page.locator('#newsletter').fill(uniqueEmail);
    await page.locator('#lastname').fill('Lovelace');
    await page.locator('#firstname').fill('Ada');
    await page.locator('#zipcode').fill('75001');
    await page.locator('#newsletter-form button[name="sign_newsletter"]').click();

    await expect(page).toHaveURL(/inscription__nl=success/);
    // The same confirmation text also exists (hidden) in the footer
    // newsletter popup markup on every page - scope to the first match.
    await expect(page.getByText('Merci de vous être inscrit').first()).toBeVisible();

    const calls = await getSalesforceCalls(request, salesforceTestId);
    const contactCall = calls.find(
      (call) => call.method === 'POST' && call.url.includes('sobjects/Contact/'),
    );

    expect(contactCall).toBeTruthy();
    const payload = JSON.parse(contactCall.body);
    expect(payload.Email).toBe(uniqueEmail);
    expect(payload.FirstName).toBe('Ada');
    expect(payload.LastName).toBe('Lovelace');
    expect(payload.Optin_Actionaute_Newsletter_mensuelle__c).toBe(true);
  });

  test('does not call Salesforce when Turnstile verification fails', async ({
    page,
    request,
    gotoWithoutCookieOverlay,
    salesforceTestId,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-newsletter-rejected-token' });

    await gotoWithoutCookieOverlay(NEWSLETTER_PATH);
    await setServerSideTurnstileResult(page, NEWSLETTER_FORM, { success: false });

    await page.locator('#newsletter').fill(`e2e-${Date.now()}@example.test`);
    await page.locator('#lastname').fill('Lovelace');
    await page.locator('#firstname').fill('Ada');
    await page.locator('#zipcode').fill('75001');
    await page.locator('#newsletter-form button[name="sign_newsletter"]').click();

    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect(page).not.toHaveURL(/inscription__nl=success/);

    const calls = await getSalesforceCalls(request, salesforceTestId);
    expect(calls.filter((call) => call.url.includes('sobjects/Contact/'))).toHaveLength(0);
  });
});
