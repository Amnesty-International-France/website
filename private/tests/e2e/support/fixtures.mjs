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

  // The exit popin's inactivity timer runs on touch devices (mobile-chromium),
  // and Playwright's clicks do not reset it: a spec staying 10 s on a page
  // could get the popin over the elements it clicks next. Mark the popin as
  // already shown in every tab. exit-popin.spec.mjs turns this off with
  // test.use({ exitPopinAlreadyShown: false }).
  exitPopinAlreadyShown: [true, { option: true }],

  exitPopinFlag: [async ({ context, exitPopinAlreadyShown }, use) => {
    if (exitPopinAlreadyShown) {
      await context.addInitScript(() => {
        try {
          // Same key as STORAGE_KEY in src/scripts/modules/exit-popin.js.
          window.sessionStorage.setItem('aif_exit_popin_shown', 'true');
        } catch {
          // Opaque-origin frames (about:blank, sandboxed iframes) have no storage.
        }
      });
    }
    await use();
  }, { auto: true }],
});
