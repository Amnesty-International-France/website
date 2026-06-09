import { expect, test } from './support/fixtures.mjs';
import {
  TEST_SITE_KEY,
  expectTurnstileScriptsInHead,
  expectWidgetSiteKey,
  mockFailingTurnstile,
  mockSilentTurnstile,
  mockSuccessfulTurnstile,
} from './support/turnstile.mjs';

const FORGOT_PASSWORD_PATH = '/mot-de-passe-oublie/';
const NEWSLETTER_PATH = '/newsletter/';

const routeProtectedFormPost = async (page, path = FORGOT_PASSWORD_PATH) => {
  const posts = [];

  await page.route(`**${path}`, async (route) => {
    const request = route.request();

    if (request.method() !== 'POST') {
      await route.continue();
      return;
    }

    posts.push(request);
    await route.fulfill({
      status: 200,
      contentType: 'text/html',
      body: '<!doctype html><title>submitted</title><p>submitted</p>',
    });
  });

  return posts;
};

const fillForgottenPasswordForm = async (page) => {
  await page.getByLabel('Votre email (obligatoire) :').fill('unknown@example.org');
};

test('renders the local guard before Cloudflare and uses the dummy invisible sitekey', async ({
  page,
  gotoWithoutCookieOverlay,
}) => {
  await mockSuccessfulTurnstile(page, { delay: 5000 });

  await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);

  await expectTurnstileScriptsInHead(page, expect);
  await expectWidgetSiteKey(page, expect, TEST_SITE_KEY);
});

test('waits for a Turnstile token before submitting the protected form', async ({
  page,
  gotoWithoutCookieOverlay,
}) => {
  await mockSuccessfulTurnstile(page, {
    delay: 500,
    token: 'mock-success-token',
    trigger: 'submit',
  });
  const posts = await routeProtectedFormPost(page);

  await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);
  await fillForgottenPasswordForm(page);

  const submitter = page.locator('#submit-btn');
  await submitter.click();

  await expect(page.getByRole('alert')).toContainText('vérification de sécurité est en cours');
  await expect(submitter).toBeDisabled();
  await expect(submitter).toHaveAttribute('aria-busy', 'true');

  await expect.poll(() => posts.length).toBe(1);

  const postData = posts[0].postData() || '';
  expect(decodeURIComponent(postData)).toContain('email=unknown@example.org');
  expect(decodeURIComponent(postData)).toContain('cf-turnstile-response=mock-success-token');
});

test('preserves the named newsletter submitter during delayed resubmission', async ({
  page,
  gotoWithoutCookieOverlay,
}) => {
  await mockSuccessfulTurnstile(page, {
    delay: 500,
    token: 'mock-newsletter-token',
    trigger: 'submit',
  });
  const posts = await routeProtectedFormPost(page, NEWSLETTER_PATH);

  await gotoWithoutCookieOverlay(NEWSLETTER_PATH);
  await page.locator('#newsletter').fill('lead@example.org');
  await page.locator('#lastname').fill('Doe');
  await page.locator('#firstname').fill('Jane');
  await page.locator('#zipcode').fill('75001');
  await page.locator('#newsletter-form button[name="sign_newsletter"]').click();

  await expect(page.locator('#newsletter-form [data-turnstile-client-error]')).toContainText(
    'vérification de sécurité est en cours',
  );
  await expect.poll(() => posts.length).toBe(1);

  const postData = decodeURIComponent(posts[0].postData() || '');
  expect(postData).toContain('newsletter=lead@example.org');
  expect(postData).toContain('sign_newsletter=');
  expect(postData).toContain('cf-turnstile-response=mock-newsletter-token');
});

test('shows a failure message when Turnstile reports an error and does not submit', async ({
  page,
  gotoWithoutCookieOverlay,
}) => {
  await mockFailingTurnstile(page, { delay: 500, trigger: 'submit' });
  const posts = await routeProtectedFormPost(page);

  await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);
  await fillForgottenPasswordForm(page);
  await page.locator('#submit-btn').click();

  await expect(page.getByRole('alert')).toContainText(
    'La vérification de sécurité n’a pas pu être effectuée',
  );
  await page.waitForTimeout(500);
  expect(posts).toHaveLength(0);
});

test('shows a failure message when Turnstile never creates a token', async ({
  page,
  gotoWithoutCookieOverlay,
}) => {
  await mockSilentTurnstile(page);
  const posts = await routeProtectedFormPost(page);

  await gotoWithoutCookieOverlay(FORGOT_PASSWORD_PATH);
  await fillForgottenPasswordForm(page);
  await page.locator('#submit-btn').click();

  await expect(page.getByRole('alert')).toContainText(
    'La vérification de sécurité est en cours',
  );
  await expect(page.getByRole('alert')).toContainText(
    'La vérification de sécurité n’a pas pu être effectuée',
    { timeout: 5000 },
  );
  expect(posts).toHaveLength(0);
});
