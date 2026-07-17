import { expect, test as base } from '@playwright/test';

export { expect };

export const test = base.extend({
  gotoWithoutCookieOverlay: async ({ page }, use) => {
    await use(async (path) => {
      await page.goto(path, { waitUntil: 'domcontentloaded' });
      await page.addStyleTag({
        content: `
          #onetrust-consent-sdk,
          .onetrust-pc-dark-filter {
            display: none !important;
            pointer-events: none !important;
          }
        `,
      });
    });
  },

  // Unique per-test id, sent as an X-AIF-E2E-Test-Id header on every request
  // the browser makes. aif-e2e-support.php's Salesforce call mock namespaces
  // its recorded calls by this header instead of a single shared WP option -
  // without it, two Salesforce-touching tests running concurrently (in
  // different Playwright workers, or the same spec across the
  // chromium/mobile-chromium projects) would interleave/overwrite each
  // other's recorded calls. Pass this same id explicitly to
  // getSalesforceCalls()/resetSalesforceCalls() (support/salesforce.mjs),
  // since the `request` fixture is a separate HTTP client that doesn't
  // inherit the page context's extra headers.
  salesforceTestId: async ({ context }, use, testInfo) => {
    const testId = testInfo.testId;
    await context.setExtraHTTPHeaders({ 'X-AIF-E2E-Test-Id': testId });
    await use(testId);
  },
});
