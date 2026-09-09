import { expect, test } from './support/fixtures';
import {
  TEST_SITE_KEY,
  mockSuccessfulTurnstile,
  setServerSideTurnstileResult,
} from './support/turnstile';
import { getJetpackEffects, resetJetpackEffects } from './support/jetpack-effects';

const FONDATION_PATH = '/fondation/';

// Same situation as the legs form (see legacy-giving-form.spec.mjs): the real
// content is a Jetpack Forms block only living in production's DB, recreated
// by seed-wordpress.sh. Here only Nom/Prénom/E-mail are required, and there's
// no consent field.
test.describe('foundation contact form', () => {
  test('is reachable directly on the page and shows its fields', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page);
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await expect(form.locator('.cf-turnstile')).toHaveAttribute('data-sitekey', TEST_SITE_KEY);
    await expect(form.locator('.cf-turnstile')).toHaveAttribute(
      'data-appearance',
      'interaction-only',
    );
    await expect(form.getByLabel(/^Nom/)).toBeVisible();
    await expect(form.getByLabel('Prénom')).toBeVisible();
    await expect(form.getByLabel('E-mail')).toBeVisible();
    await expect(form.getByLabel('Téléphone')).toBeVisible();
    await expect(form.getByLabel('Un message à nous laisser ?')).toBeVisible();
    await expect(form.getByRole('button', { name: 'Envoyer' })).toBeVisible();
  });

  test('lets a visitor fill in the optional and required fields', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page);
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await setServerSideTurnstileResult(page, 'div[data-test="contact-form"] form', {
      success: true,
    });
    await form.getByLabel('Monsieur', { exact: true }).check();
    await form.getByLabel(/^Nom/).fill('Turing');
    await form.getByLabel('Prénom').fill('Alan');
    await form.getByLabel('E-mail').fill('alan@example.test');
    await form.getByLabel('Téléphone').fill('0102030405');
    await form.getByLabel('Un message à nous laisser ?').fill('Une question sur la Fondation.');
    await form.getByLabel(/Je souhaite recevoir des informations/).check();

    await expect(form.getByLabel('Monsieur', { exact: true })).toBeChecked();
    await expect(form.getByLabel(/^Nom/)).toHaveValue('Turing');
    await expect(form.getByLabel('E-mail')).toHaveValue('alan@example.test');
    await expect(form.getByLabel(/Je souhaite recevoir des informations/)).toBeChecked();
  });

  test('submits with only the required fields and shows the real Jetpack success message', async ({
    page,
    request,
    salesforceTestId: e2eTestId,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page);
    await resetJetpackEffects(request, e2eTestId);
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await setServerSideTurnstileResult(page, 'div[data-test="contact-form"] form', {
      success: true,
    });
    await form.getByLabel(/^Nom/).fill('Turing');
    await form.getByLabel('Prénom').fill('Alan');
    await form.getByLabel('E-mail').fill('alan@example.test');

    await form.getByRole('button', { name: 'Envoyer' }).click();

    await expect(page.getByText('Merci pour votre réponse')).toBeVisible();
    await expect
      .poll(() => getJetpackEffects(request, e2eTestId))
      .toEqual({
        feedback_count: 1,
        mail_count: 1,
      });
  });

  test('waits for an interaction-only Turnstile token before submitting', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    const jetpackPosts = [];
    page.on('request', (request) => {
      const url = new URL(request.url());

      if (
        request.method() === 'POST' &&
        url.pathname.endsWith('/wp-admin/admin-ajax.php') &&
        url.searchParams.get('action') === 'grunion-contact-form'
      ) {
        jetpackPosts.push(request);
      }
    });
    await mockSuccessfulTurnstile(page, {
      delay: 5000,
      token: 'mock-jetpack-interaction-token',
      trigger: 'submit',
    });
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await setServerSideTurnstileResult(page, 'div[data-test="contact-form"] form', {
      success: true,
    });
    await form.getByLabel(/^Nom/).fill('Turing');
    await form.getByLabel('Prénom').fill('Alan');
    await form.getByLabel('E-mail').fill('alan@example.test');

    const submitter = form.getByRole('button', { name: 'Envoyer' });
    await submitter.click();

    await expect(form.locator('[data-turnstile-client-error]')).toContainText(
      'vérification de sécurité est en cours',
    );
    await expect(submitter).toBeDisabled();
    await expect(submitter).toHaveAttribute('aria-busy', 'true');
    await page.waitForTimeout(1000);
    expect(jetpackPosts).toHaveLength(0);

    await expect(page.getByText('Merci pour votre réponse')).toBeVisible();
    expect(jetpackPosts).toHaveLength(1);
  });

  test('does not save feedback, send mail, or confirm a submission rejected by server-side Turnstile', async ({
    page,
    request,
    salesforceTestId: e2eTestId,
    gotoWithoutCookieOverlay,
  }) => {
    await mockSuccessfulTurnstile(page);
    await resetJetpackEffects(request, e2eTestId);
    const effectsBeforeSubmission = await getJetpackEffects(request, e2eTestId);
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await setServerSideTurnstileResult(page, 'div[data-test="contact-form"] form', {
      success: false,
    });
    await form.getByLabel(/^Nom/).fill('Turing');
    await form.getByLabel('Prénom').fill('Alan');
    await form.getByLabel('E-mail').fill('alan@example.test');

    await form.getByRole('button', { name: 'Envoyer' }).click();

    await expect(page.getByText(/La vérification de sécurité a échoué/)).toBeVisible();
    await expect(page.getByText('Merci pour votre réponse')).not.toBeVisible();
    await expect.poll(() => getJetpackEffects(request, e2eTestId)).toEqual(effectsBeforeSubmission);
  });
});
