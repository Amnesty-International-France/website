import { expect, test } from './support/fixtures';

const LEGS_PATH = '/legs/';

// The real "formulaire-legs" content is a Jetpack Forms block only living in
// production's DB; seed-wordpress.sh recreates it via Jetpack's classic
// [contact-form] shortcode, which genuinely renders and processes a real
// AJAX submission (Jetpack treats "localhost" as offline/dev mode).
const fillRequiredFields = async (form) => {
  await form.getByLabel('Madame', { exact: true }).check();
  // Jetpack's required-marker "(obligatoire)" is inside the <label>, so a
  // plain "Nom" match would be ambiguous with "Prénom" - anchor with ^.
  await form.getByLabel(/^Nom/).fill('Lovelace');
  await form.getByLabel('Prénom').fill('Ada');
  await form.getByLabel('Adresse').fill('1 rue de Rivoli');
  await form.getByLabel('Code Postal').fill('75001');
  await form.getByLabel('Ville').fill('Paris');
  await form.getByLabel('E-mail').fill('ada@example.test');
  await form.getByLabel('Téléphone').fill('0102030405');
  // No accessible name on the consent checkbox (its GDPR text renders
  // separately, below the submit button) - target it via its class instead.
  await form.locator('input.consent[type="checkbox"]').check();
};

test.describe('legacy giving (legs) brochure request', () => {
  test('reveals the brochure request form from the sticky call-to-action', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(LEGS_PATH);

    await page.getByRole('link', { name: 'Demander notre brochure' }).click();

    await expect(page).toHaveURL(/#legs-form$/);

    const form = page.locator('#legs-form');
    await expect(form.getByLabel(/^Nom/)).toBeVisible();
    await expect(form.getByLabel('Prénom')).toBeVisible();
    await expect(form.getByLabel('Adresse')).toBeVisible();
    await expect(form.getByLabel('Code Postal')).toBeVisible();
    await expect(form.getByLabel('Ville')).toBeVisible();
    await expect(form.getByLabel('E-mail')).toBeVisible();
    await expect(form.getByLabel('Téléphone')).toBeVisible();
    await expect(form.getByRole('button', { name: 'Envoyer' })).toBeVisible();
  });

  test('lets a visitor fill in the brochure request form, including civility and delivery preference', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(LEGS_PATH);

    const form = page.locator('#legs-form');
    await fillRequiredFields(form);
    await form.getByLabel('Par email', { exact: true }).check();

    await expect(form.getByLabel('Madame', { exact: true })).toBeChecked();
    await expect(form.getByLabel(/^Nom/)).toHaveValue('Lovelace');
    await expect(form.getByLabel('E-mail')).toHaveValue('ada@example.test');
    await expect(form.getByLabel('Par email', { exact: true })).toBeChecked();
    await expect(form.getByLabel('Par courrier postal')).not.toBeChecked();
  });

  test('submits the brochure request and shows the real Jetpack success message', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(LEGS_PATH);

    const form = page.locator('#legs-form');
    await fillRequiredFields(form);

    await form.getByRole('button', { name: 'Envoyer' }).click();

    await expect(page.getByText('Merci pour votre réponse')).toBeVisible();
  });
});
