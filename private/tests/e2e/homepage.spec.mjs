import { expect, test } from './support/fixtures';

test.describe('homepage', () => {
  test('loads the static front page with its content', async ({ page, gotoWithoutCookieOverlay }) => {
    await gotoWithoutCookieOverlay('/');

    await expect(
      page.getByRole('heading', { level: 1, name: 'Bienvenue chez Amnesty International France' }),
    ).toBeVisible();
    await expect(page.getByRole('contentinfo')).toBeVisible();
  });
});
