import { expect, test } from './support/fixtures';

// The on-site search trigger ("Ouvrir la recherche") is Jetpack Instant
// Search, a paid module (requires a live WordPress.com connection) that
// can't be unlocked in this offline e2e stack - unlike the free Jetpack
// Forms module used by legacy-giving-form.spec.mjs/foundation-form.spec.mjs.
// These specs instead drive WordPress's own native pretty-permalink search
// URL (/search/<term>/) directly. Its results page needed its own fix: see
// the "wp_template" seeded in seed-wordpress.sh for why (the theme has no
// templates/search.html, so is_search() fell back to an empty, unreliable
// template without it).
test.describe('search', () => {
  test('shows matching results for a real query', async ({ page, gotoWithoutCookieOverlay }) => {
    await gotoWithoutCookieOverlay('/search/amnesty/');

    // The real shipped French translation (amnesty-fr_FR.po) is "%s
    // resultats nom (%s)" - inconsistent accenting (plural drops the accent
    // on "résultat(s)") and an odd "nom (term)" suffix, but that's the real
    // production copy, not an e2e artifact - matched loosely here.
    await expect(page.getByRole('heading', { name: /r[ée]sultats?.*\(amnesty\)/i })).toBeVisible();
    await expect(page.locator('.post--result').first()).toBeVisible();
  });

  test('shows a zero-results heading for a query with no matches, without erroring', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay('/search/xyznonexistentqueryterm123/');

    await expect(
      page.getByRole('heading', { name: /0 r[ée]sultats?.*\(xyznonexistentqueryterm123\)/i }),
    ).toBeVisible();
    await expect(page.locator('body')).not.toContainText('Fatal error');
  });
});
