import { expect, test } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

const CONTACT_PAGE = '/mon-espace/e2e-contact-twice/?aif_e2e_warm_contact_pattern=1';
const LOGIN_FORM = '.login-form form.aif-form-container';

const login = async (page, goto, editor = false) => {
  await mockSuccessfulTurnstile(page, { token: 'mock-contact-login' });
  await goto('/connectez-vous/');
  await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
  await page.locator(`${LOGIN_FORM} #email`).fill(editor ? 'e2e-legacy-editor-donor@example.test' : 'e2e-verified-donor@example.test');
  await page.locator(`${LOGIN_FORM} #password`).fill(editor ? 'E2e-Legacy-Editor-1!' : 'E2e-Verified-1!');
  await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();
  await expect(page).toHaveURL(/\/mon-espace\/$/);
};

test('enumerates the contact pattern through the real WordPress registry without Salesforce', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay, true);
  await resetSalesforceCalls(request, salesforceTestId);

  const result = await page.evaluate(async () => {
    const nonce = await (await fetch('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
    const response = await fetch('/wp-json/aif-e2e/v1/contact-patterns', {
      headers: { 'X-WP-Nonce': nonce },
    });
    const patterns = await response.json();
    const blockResponse = await fetch('/wp-json/wp/v2/block-types/amnesty-core/donor-contact-form', {
      headers: { 'X-WP-Nonce': nonce },
    });
    const block = await blockResponse.json();
    return {
      status: response.status,
      pattern: patterns.find((pattern) => pattern.name === 'amnesty/contact-us'),
      blockStatus: blockResponse.status,
      apiVersion: block.api_version,
      dynamic: block.is_dynamic,
    };
  });

  expect(result.status).toBe(200);
  expect(result.pattern.content).toContain('amnesty-core/donor-contact-form');
  expect(result.pattern.content).not.toContain('contact_nonce');
  expect(result.blockStatus).toBe(200);
  expect(result.apiVersion).toBe(3);
  expect(result.dynamic).toBe(true);
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});

test('renders after catalogue warming and submits one Case for two rendered forms', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay);
  await gotoWithoutCookieOverlay(`${CONTACT_PAGE}&subject=${encodeURIComponent("Demande d'un reçu")}`);
  const forms = page.locator('form:has(#subject)');
  await expect(forms).toHaveCount(2);
  await expect(forms.first().locator('#subject')).toHaveValue("Demande d'un reçu");
  await forms.first().locator('#message').fill('Demande de contact de test');
  await resetSalesforceCalls(request, salesforceTestId);

  await forms.first().getByRole('button', { name: 'Enregistrer' }).click();

  await expect(page.getByText('Votre demande de contact à bien été prise en compte')).toHaveCount(2);
  const calls = await getSalesforceCalls(request, salesforceTestId);
  const cases = calls.filter((call) => call.method === 'POST' && call.url.endsWith('/sobjects/Case'));
  expect(cases).toHaveLength(1);
  expect(JSON.parse(cases[0].body).Subject).toBe("Demande d'un reçu");
});

test('rejects a contact POST with an invalid nonce without creating a Case', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay);
  await gotoWithoutCookieOverlay(CONTACT_PAGE);
  const form = page.locator('form:has(#subject)').first();
  await form.locator('#subject').fill('Nonce invalide');
  await form.locator('#message').fill('Ne pas envoyer');
  await form.locator('[name="contact_nonce"]').evaluate((input) => input.setAttribute('value', 'wrong-nonce'));
  await resetSalesforceCalls(request, salesforceTestId);
  const responsePromise = page.waitForResponse((response) => response.request().method() === 'POST' && response.url().includes('/mon-espace/e2e-contact-twice/'));

  await form.getByRole('button', { name: 'Enregistrer' }).click();

  expect((await responsePromise).status()).toBe(403);
  expect(await getSalesforceCalls(request, salesforceTestId)).not.toEqual(expect.arrayContaining([
    expect.objectContaining({ method: 'POST' }),
  ]));
});

test('lets an editor preview without donor context or Salesforce', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay, true);
  await resetSalesforceCalls(request, salesforceTestId);

  await gotoWithoutCookieOverlay(`${CONTACT_PAGE}&preview=true`);

  await expect(page.getByText('Le formulaire est disponible dans votre espace donateur.')).toHaveCount(2);
  await expect(page.locator('form:has(#subject)')).toHaveCount(0);
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});

test('renders the registered donor contact preview inside the real iframe editor without Salesforce', async ({
  page, request, salesforceTestId,
}, testInfo) => {
  // wp-admin needs its native auth cookie, separate from the public login.
  await page.goto('/wp-login.php');
  const username = page.locator('#user_login');
  const password = page.locator('#user_pass');
  // Core's delayed autofocus must finish before filling the password field.
  await expect(username).toBeFocused();
  await username.fill('e2e-legacy-editor-donor@example.test');
  await password.fill('E2e-Legacy-Editor-1!');
  await expect(username).toHaveValue('e2e-legacy-editor-donor@example.test');
  await expect(password).toHaveValue('E2e-Legacy-Editor-1!');
  await page.locator('#wp-submit').click();
  await expect(page).toHaveURL(/\/wp-admin\//);
  const postId = await page.evaluate(async () => {
    const response = await fetch('/wp-json/wp/v2/pages?slug=e2e-contact-twice&_fields=id');
    return (await response.json())[0].id;
  });
  await resetSalesforceCalls(request, salesforceTestId);

  const response = await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`);

  expect(response.status()).toBe(200);
  await expect.poll(() => page.evaluate(() => window.wp?.data?.select('core/editor')?.getCurrentPostId())).toBe(postId);
  await expect(page.locator('iframe[name="editor-canvas"]')).toHaveCount(1);
  await expect.poll(() => page.evaluate(() => window.wp.blocks.getBlockType('amnesty-core/donor-contact-form')?.apiVersion)).toBe(3);
  const renderedPreview = page.waitForResponse((result) => result.url().includes('/wp/v2/block-renderer/amnesty-core/donor-contact-form'));
  await page.evaluate(() => {
    const block = window.wp.blocks.createBlock('amnesty-core/donor-contact-form');
    window.wp.data.dispatch('core/block-editor').resetBlocks([block]);
  });
  const previewResponse = await renderedPreview;
  expect(previewResponse.status()).toBe(200);
  const block = page.frameLocator('iframe[name="editor-canvas"]').locator('[data-type="amnesty-core/donor-contact-form"]');
  await expect(block).toBeVisible();
  await expect(block.getByText('Le formulaire est disponible dans votre espace donateur.')).toBeVisible();
  await expect(block.locator('form')).toHaveCount(0);
  const calls = await getSalesforceCalls(request, salesforceTestId);
  expect(calls).toEqual([]);
  const previewUrl = new URL(previewResponse.url());
  await testInfo.attach('donor-contact-iframe-proof', {
    body: JSON.stringify({
      project: testInfo.project.name,
      apiVersion: await page.evaluate(() => window.wp.blocks.getBlockType('amnesty-core/donor-contact-form').apiVersion),
      rendererStatus: previewResponse.status(),
      rendererContext: previewUrl.searchParams.get('context'),
      rendererHasPostId: previewUrl.searchParams.has('post_id'),
      blockVisible: await block.isVisible(),
      placeholderVisible: await block.getByText('Le formulaire est disponible dans votre espace donateur.').isVisible(),
      forms: await block.locator('form').count(),
      salesforceCalls: calls.length,
    }),
    contentType: 'application/json',
  });
  await testInfo.attach('donor-contact-iframe', { body: await block.screenshot(), contentType: 'image/png' });
});

test('accepts the REST edit context from the URL or JSON body without Salesforce', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}, testInfo) => {
  await login(page, gotoWithoutCookieOverlay, true);
  await resetSalesforceCalls(request, salesforceTestId);

  const results = await page.evaluate(async () => {
    const nonce = await (await fetch('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
    const pages = await (await fetch('/wp-json/wp/v2/pages?slug=e2e-contact-twice&_fields=id')).json();
    const endpoint = '/wp-json/wp/v2/block-renderer/amnesty-core/donor-contact-form';
    const variants = [
      { name: 'GET query context', method: 'GET', url: `${endpoint}?context=edit` },
      { name: 'POST query context', method: 'POST', url: `${endpoint}?context=edit`, body: { attributes: {} } },
      { name: 'POST JSON context', method: 'POST', url: endpoint, body: { context: 'edit', attributes: {} } },
      { name: 'POST JSON context with page', method: 'POST', url: endpoint, body: { context: 'edit', attributes: {}, post_id: pages[0].id } },
      { name: 'GET page editor content', method: 'GET', url: `/wp-json/wp/v2/pages/${pages[0].id}?context=edit` },
    ];
    return Promise.all(variants.map(async (variant) => {
      const response = await fetch(variant.url, {
        method: variant.method,
        headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
        ...(variant.body ? { body: JSON.stringify(variant.body) } : {}),
      });
      const body = await response.text();
      return {
        name: variant.name,
        status: response.status,
        placeholder: body.includes('Le formulaire est disponible dans votre espace donateur.'),
        form: body.includes('<form'),
      };
    }));
  });

  await testInfo.attach('rest-preview-contexts', { body: JSON.stringify(results), contentType: 'application/json' });
  results.forEach((result) => {
    expect.soft(result.status, result.name).toBe(200);
    expect.soft(result.placeholder, result.name).toBe(true);
    expect.soft(result.form, result.name).toBe(false);
  });
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});

test('denies an unauthenticated ServerSideRender request without contacting Salesforce', async ({
  request, salesforceTestId,
}) => {
  const pages = await (await request.get('/wp-json/wp/v2/pages?slug=e2e-contact-twice&_fields=id')).json();
  await resetSalesforceCalls(request, salesforceTestId);

  const responses = await Promise.all([
    ...['', `&post_id=${pages[0].id}`].map((postQuery) => (
      request.get(`/wp-json/wp/v2/block-renderer/amnesty-core/donor-contact-form?context=edit${postQuery}`, {
        headers: { 'X-AIF-E2E-Test-Id': salesforceTestId },
      })
    )),
    ...[undefined, pages[0].id].map((postId) => request.post('/wp-json/wp/v2/block-renderer/amnesty-core/donor-contact-form', {
      headers: { 'X-AIF-E2E-Test-Id': salesforceTestId },
      data: { context: 'edit', attributes: {}, ...(postId ? { post_id: postId } : {}) },
    })),
  ]);
  responses.forEach((response) => expect([401, 403]).toContain(response.status()));
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});
