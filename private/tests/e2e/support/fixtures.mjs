import { expect, test as base } from '@playwright/test';

export { expect };

// Unique-enough email for tests that need one to avoid colliding with a
// previous run's data (e.g. Salesforce Contact lookups keyed by email).
export const uniqueEmail = () => `e2e-${Date.now()}-${Math.floor(Math.random() * 1e6)}@example.test`;

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
  // auto: true so every test's context gets the header unconditionally - a
  // spec that starts asserting on Salesforce calls without thinking to
  // destructure this fixture would otherwise fall back to a shared "default"
  // bucket server-side, silently reintroducing the interleaving this exists
  // to prevent.
  salesforceTestId: [async ({ context }, use, testInfo) => {
    const testId = testInfo.testId;
    await context.setExtraHTTPHeaders({ 'X-AIF-E2E-Test-Id': testId });
    await use(testId);
  }, { auto: true }],
});
