import { expect, test } from './support/fixtures.mjs';
import { TEST_SITE_KEY, expectWidgetSiteKey } from './support/turnstile.mjs';

const runCloudflareSmoke = process.env.RUN_CLOUDFLARE_SMOKE === '1';
const FAILING_SECRET = '2x0000000000000000000000000000000AA';
const UNKNOWN_EMAIL = 'unknown@example.org';

test.describe('@cloudflare-smoke Turnstile dummy keys', () => {
  test.skip(!runCloudflareSmoke, 'Set RUN_CLOUDFLARE_SMOKE=1 to call Cloudflare dummy Turnstile.');

  test('continues to the business response with Cloudflare passing dummy keys', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay('/mot-de-passe-oublie/');

    await expectWidgetSiteKey(page, expect, TEST_SITE_KEY);
    await expect(page.locator('[name="cf-turnstile-response"]')).toHaveValue(/.+/, {
      timeout: 10000,
    });
    await page.getByLabel('Votre email (obligatoire) :').fill(UNKNOWN_EMAIL);
    await page.locator('#submit-btn').click();

    await expect(page.getByText('Votre utilisateur nous est inconnu')).toBeVisible();
    await expect(page.getByText('La vérification de sécurité a échoué.')).toHaveCount(0);
  });

  test('stops on the Turnstile error with Cloudflare failing dummy secret', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay('/mot-de-passe-oublie/');

    await expectWidgetSiteKey(page, expect, TEST_SITE_KEY);
    await expect(page.locator('[name="cf-turnstile-response"]')).toHaveValue(/.+/, {
      timeout: 10000,
    });
    await page.locator('form.aif-form-container').evaluate((form, secret) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'aif_e2e_turnstile_secret_key';
      input.value = secret;
      form.appendChild(input);
    }, FAILING_SECRET);
    await page.getByLabel('Votre email (obligatoire) :').fill(UNKNOWN_EMAIL);
    await page.locator('#submit-btn').click();

    await expect(
      page.getByText('La vérification de sécurité a échoué.', { exact: true }),
    ).toBeVisible();
    await expect(page.getByText('Votre utilisateur nous est inconnu')).toHaveCount(0);
  });
});
