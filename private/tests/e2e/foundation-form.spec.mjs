import { expect, test } from './support/fixtures';

const FONDATION_PATH = '/fondation/';

// The real "formulaire-foundation" content (rendered at
// /fondation-amnesty-international-france/ in production) is a Jetpack
// Forms block, same situation as the legs form: its markup only exists in
// production's database, so seed-wordpress.sh recreates it with the real
// field labels via Jetpack's classic [contact-form] shortcode. Unlike the
// legs page, this form has no scroll-to CTA - it's a plain always-visible
// section - and only Nom/Prénom/E-mail are required (Civilité, Téléphone,
// the message, and the postal-mail checkbox are all optional, no consent
// field at all).
test.describe('foundation contact form', () => {
  test('is reachable directly on the page and shows its fields', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
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
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
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
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(FONDATION_PATH);

    const form = page.locator('div[data-test="contact-form"]');
    await form.getByLabel(/^Nom/).fill('Turing');
    await form.getByLabel('Prénom').fill('Alan');
    await form.getByLabel('E-mail').fill('alan@example.test');

    await form.getByRole('button', { name: 'Envoyer' }).click();

    await expect(page.getByText('Merci pour votre réponse')).toBeVisible();
  });
});
