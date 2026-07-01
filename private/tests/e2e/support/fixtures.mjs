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
});
