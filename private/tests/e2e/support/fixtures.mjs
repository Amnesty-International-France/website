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

  // Unique per-test id, sent as an X-AIF-E2E-Test-Id header on every browser
  // request so aif-e2e-support.php's Salesforce call mock can namespace its
  // log per test instead of a single shared option - otherwise concurrent
  // tests would interleave each other's calls. The `request` fixture doesn't
  // inherit these headers, so pass this id explicitly to
  // getSalesforceCalls()/resetSalesforceCalls() (support/salesforce.mjs).
  salesforceTestId: async ({ context }, use, testInfo) => {
    const testId = testInfo.testId;
    await context.setExtraHTTPHeaders({ 'X-AIF-E2E-Test-Id': testId });
    await use(testId);
  },
});
