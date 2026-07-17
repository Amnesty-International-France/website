import { expect, test } from './support/fixtures';

test.describe('main navigation', () => {
  test('lets a visitor reach the petitions archive from the main menu', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay('/');

    const mobileMenuToggle = page.locator('button.burger');
    const isMobileNavigation = await mobileMenuToggle.isVisible();
    const navigation = isMobileNavigation
      ? page.locator('#mobile-menu')
      : page.locator('.desktop-nav');

    if (isMobileNavigation) {
      await mobileMenuToggle.click();
      await expect(navigation).toBeVisible();
    }

    await navigation.locator('a[href$="/petitions/"]').click();

    await expect(page).toHaveURL(/\/petitions\/?$/);
  });
});
