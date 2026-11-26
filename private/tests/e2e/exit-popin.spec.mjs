import { expect, test } from './support/fixtures';

const popin = (page) => page.locator('dialog.exit-popin');

const leaveThroughTop = async (page) => {
  await page.mouse.move(640, 300);
  await page.mouse.move(640, -10, { steps: 5 });
};

test.describe('exit popin', () => {
  test.use({ exitPopinAlreadyShown: false });

  test('opens on a desktop exit gesture, once per tab', async ({
    page,
    context,
    gotoWithoutCookieOverlay,
  }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium', 'Mouse exit gesture: desktop only.');

    await gotoWithoutCookieOverlay('/');
    await leaveThroughTop(page);
    await expect(popin(page)).toBeVisible();
    await expect(
      popin(page).getByRole('heading', { name: 'PSSST... AVANT DE PARTIR !' }),
    ).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(popin(page)).toHaveCount(0);

    await page.reload({ waitUntil: 'domcontentloaded' });
    await leaveThroughTop(page);
    await page.waitForTimeout(1500);
    await expect(popin(page)).toHaveCount(0);

    const newTab = await context.newPage();
    await newTab.goto('/', { waitUntil: 'domcontentloaded' });
    await leaveThroughTop(newTab);
    await expect(popin(newTab)).toBeVisible();
  });

  test('opens after 10 s of inactivity on a phone and fits its screen', async ({
    page,
    gotoWithoutCookieOverlay,
  }, testInfo) => {
    test.skip(
      testInfo.project.name !== 'mobile-chromium',
      'Inactivity trigger: touch devices only.',
    );

    await page.setViewportSize({ width: 375, height: 812 });
    await page.clock.install();
    await gotoWithoutCookieOverlay('/');
    expect(await page.evaluate(() => window.matchMedia('(pointer: coarse)').matches)).toBe(true);

    await page.clock.runFor(9000);
    await expect(popin(page)).toHaveCount(0);
    await page.clock.runFor(1000);
    await expect(popin(page)).toBeVisible();

    await expect(
      popin(page).getByRole('heading', { name: 'PSSST... AVANT DE PARTIR !' }),
    ).toBeInViewport({
      ratio: 1,
    });
    await expect(popin(page).getByRole('link', { name: /je fais un don/i })).toBeInViewport({
      ratio: 1,
    });
  });
});
