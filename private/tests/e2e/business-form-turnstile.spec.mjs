import { expect, test, uniqueEmail } from './support/fixtures';
import { getClhState, setupBusinessFormFixtures } from './support/business-forms';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';

test.describe('non-Jetpack business forms rejected by server-side Turnstile', () => {
  let fixtures;

  test.beforeEach(async ({ request, salesforceTestId }) => {
    fixtures = await setupBusinessFormFixtures(request, salesforceTestId);
    expect(fixtures.ready).toBe(true);
    await resetSalesforceCalls(request, salesforceTestId);
  });

  test('does not call Salesforce for a rejected Chronicle request', async ({
    page,
    request,
    gotoWithoutCookieOverlay,
    salesforceTestId,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-chronicle-rejected-token' });
    await gotoWithoutCookieOverlay(fixtures.chronicle_path);

    const form = '#discover-chronicle-form';
    await setServerSideTurnstileResult(page, form, { success: false });
    await page.locator('#lastname').fill('Lovelace');
    await page.locator('#firstname').fill('Ada');
    await page.locator('#street-address').fill('1 rue des Droits humains');
    await page.locator('#zipcode').fill('75001');
    await page.locator('#city').fill('Paris');
    await page.locator('#email').fill(uniqueEmail());
    await page.getByRole('button', { name: 'Envoyer ma demande' }).click();

    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect(page).not.toHaveURL(/inscription_chronique=success/);
    await expect.poll(() => getSalesforceCalls(request, salesforceTestId)).toEqual([]);
  });

  test('does not search for a donor after a rejected e-mail check', async ({
    page,
    request,
    gotoWithoutCookieOverlay,
    salesforceTestId,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-donor-rejected-token' });
    await gotoWithoutCookieOverlay(fixtures.donor_path);

    const form = 'form.aif-form-container';
    await setServerSideTurnstileResult(page, form, { success: false });
    await page.locator('input[name="email"]').fill(uniqueEmail());
    await page.getByRole('button', { name: 'Rechercher' }).click();

    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect.poll(() => getSalesforceCalls(request, salesforceTestId)).toEqual([]);
  });

  test('does not mutate the CLH skipped-petition state after a rejected skip', async ({
    context,
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page, { token: 'mock-clh-skip-rejected-token' });
    await gotoWithoutCookieOverlay(fixtures.tunnel_path);

    const form = 'form.tunnel-clh-skip-form';
    await expect(page.locator(form)).toHaveCount(1);
    const petitionId = Number(await page.locator(`${form} input[name="petition_id"]`).inputValue());
    expect(petitionId).toBe(fixtures.petition_id);

    const stateBefore = await getClhState(page);
    expect(stateBefore.skipped_petitions).not.toContain(petitionId);
    await setServerSideTurnstileResult(page, form, { success: false });
    await page.locator('button[name="skip_petition"]:visible').click();

    await expect(page).toHaveURL(/signature_status=turnstile/);
    await expect(page.getByText('La vérification de sécurité a échoué')).toBeVisible();
    await expect.poll(() => getClhState(page)).toEqual(stateBefore);

    const skippedCookie = (await context.cookies()).find(
      (cookie) => cookie.name === 'clh_skipped_petitions',
    );
    expect(skippedCookie).toBeUndefined();
  });
});
