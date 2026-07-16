import { expect, test } from './support/fixtures';

const DON_PATH = '/don/';

// The donation calculator never leads to an on-site cart/checkout: it only
// builds a link to an external, third-party donation platform
// (soutenir.amnesty.fr) by appending amount (in cents) and frequency query
// params to a hardcoded href. These specs verify that outbound link is built
// correctly as a visitor interacts with the calculator, without following it.
test.describe('donation page calculator', () => {
  test('defaults to the 15€ monthly recommendation and computes the after-tax amount', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(DON_PATH);

    // The header nav also embeds donation-calculator instance(s) (a popin and
    // static links) on every page; the page's own calculator is the one whose
    // link is tagged data-position="contenu" - scope everything through it to
    // avoid matching the header's duplicate #input-donation/#donation-tabs ids.
    const calculator = page
      .locator('.donation-calculator')
      .filter({ has: page.locator('a.donation-link[data-position="contenu"]') });
    await expect(calculator).toBeVisible();

    const link = calculator.locator('a.donation-link');
    await expect(link).toHaveAttribute('data-amount', '15');
    await expect(link).toHaveAttribute('data-type', 'mensuel');

    const url = new URL(await link.getAttribute('href'));
    expect(url.hostname).toBe('soutenir.amnesty.fr');
    expect(url.searchParams.get('amount')).toBe('1500');
    expect(url.searchParams.get('frequency')).toBe('regular');
  });

  test('switches to the punctual tab and updates the outbound donation link', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(DON_PATH);

    // The header nav also embeds donation-calculator instance(s) (a popin and
    // static links) on every page; the page's own calculator is the one whose
    // link is tagged data-position="contenu" - scope everything through it to
    // avoid matching the header's duplicate #input-donation/#donation-tabs ids.
    const calculator = page
      .locator('.donation-calculator')
      .filter({ has: page.locator('a.donation-link[data-position="contenu"]') });
    await calculator.locator('.donation-tabs').getByText('Don Ponctuel', { exact: true }).click();

    const link = calculator.locator('a.donation-link');
    await expect(link).toHaveAttribute('data-amount', '250');
    await expect(link).toHaveAttribute('data-type', 'ponctuel');

    const url = new URL(await link.getAttribute('href'));
    expect(url.searchParams.get('amount')).toBe('25000');
    expect(url.searchParams.get('frequency')).toBe('once');
  });

  test('recomputes the simulated price and the link amount when typing a custom value', async ({
    page,
    gotoWithoutCookieOverlay,
  }) => {
    await gotoWithoutCookieOverlay(DON_PATH);

    // The header nav also embeds donation-calculator instance(s) (a popin and
    // static links) on every page; the page's own calculator is the one whose
    // link is tagged data-position="contenu" - scope everything through it to
    // avoid matching the header's duplicate #input-donation/#donation-tabs ids.
    const calculator = page
      .locator('.donation-calculator')
      .filter({ has: page.locator('a.donation-link[data-position="contenu"]') });
    const freeInput = calculator.locator('#input-donation');
    await freeInput.fill('50');

    // 50€ - 66% tax reduction = 17.00€ -> trailing zero stripped once -> "17,0"
    await expect(calculator.locator('#donation-simulated')).toContainText('17,0 €');

    const link = calculator.locator('a.donation-link');
    const url = new URL(await link.getAttribute('href'));
    expect(url.searchParams.get('amount')).toBe('5000');
  });
});
