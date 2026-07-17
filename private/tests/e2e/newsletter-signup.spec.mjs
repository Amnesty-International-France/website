import { expect, test, uniqueEmail } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

const NEWSLETTER_PATH = '/newsletter/';
const NEWSLETTER_FORM = '#newsletter-form';

// Unlike petition signing/legs/foundation, newsletter signup makes a REAL,
// synchronous Salesforce call while handling the POST (a new email triggers
// post_salesforce_users()) - aif-e2e-support.php mocks and records it so this
// spec can assert it actually happened.

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

    const email = uniqueEmail();
    await page.locator('#newsletter').fill(email);
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
    expect(payload.Email).toBe(email);
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

    await page.locator('#newsletter').fill(uniqueEmail());
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
