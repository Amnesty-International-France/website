import { expect, test } from './support/fixtures';

const LEGS_PATH = '/legs/';

// The real "formulaire-legs" content (rendered at /nous-soutenir/legs/ in
// production) is a Jetpack Forms block whose markup only exists in
// production's database (not in this repo), and Jetpack isn't installed in
// this e2e stack to render/process a real submission. seed-wordpress.sh
// seeds static markup matching the real field labels instead (civilité,
// nom, prénom, adresse, code postal, ville, e-mail, téléphone, "je souhaite
// recevoir la brochure" x2, consent), so this spec only verifies a visitor
// can reach and fill in a representative brochure-request form from the
// sticky CTA - not an actual Jetpack submission/thank-you flow.
test.describe('legacy giving (legs) brochure request', () => {
  test('reveals the brochure request form from the sticky call-to-action', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(LEGS_PATH);

    await page.getByRole('link', { name: 'Demander notre brochure' }).click();

    await expect(page).toHaveURL(/#legs-form$/);

    const form = page.locator('#legs-form');
    await expect(form.getByLabel('Nom', { exact: true })).toBeVisible();
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
    await form.getByLabel('Madame', { exact: true }).check();
    await form.getByLabel('Nom', { exact: true }).fill('Lovelace');
    await form.getByLabel('Prénom').fill('Ada');
    await form.getByLabel('Adresse').fill('1 rue de Rivoli');
    await form.getByLabel('Code Postal').fill('75001');
    await form.getByLabel('Ville').fill('Paris');
    await form.getByLabel('E-mail').fill('ada@example.test');
    await form.getByLabel('Téléphone').fill('0102030405');
    await form.getByLabel('Par email', { exact: true }).check();
    await form.getByLabel(/J.accepte/).check();

    await expect(form.getByLabel('Madame', { exact: true })).toBeChecked();
    await expect(form.getByLabel('Nom', { exact: true })).toHaveValue('Lovelace');
    await expect(form.getByLabel('E-mail')).toHaveValue('ada@example.test');
    await expect(form.getByLabel('Par email', { exact: true })).toBeChecked();
    await expect(form.getByLabel('Par courrier postal')).not.toBeChecked();
  });
});
