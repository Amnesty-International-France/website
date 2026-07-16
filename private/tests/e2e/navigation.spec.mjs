import { expect, test } from './support/fixtures';

test.describe('main navigation', () => {
  test('lets a visitor reach the petitions archive from the main menu', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay('/');

    await page.locator('nav a[href$="/petitions/"]').first().click();

    await expect(page).toHaveURL(/\/petitions\/?$/);
  });
});
