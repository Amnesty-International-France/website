import { expect, test } from './support/fixtures';
import { mockSuccessfulTurnstile, setServerSideTurnstileResult } from './support/turnstile';
import { getSalesforceCalls, resetSalesforceCalls } from './support/salesforce';

const CONTACT_PAGE = '/mon-espace/e2e-contact-twice/?aif_e2e_warm_contact_pattern=1';
const LOGIN_FORM = '.login-form form.aif-form-container';

const login = async (page, goto) => {
  await mockSuccessfulTurnstile(page, { token: 'mock-contact-login' });
  await goto('/connectez-vous/');
  await setServerSideTurnstileResult(page, LOGIN_FORM, { success: true });
  await page.locator(`${LOGIN_FORM} #email`).fill('e2e-verified-donor@example.test');
  await page.locator(`${LOGIN_FORM} #password`).fill('E2e-Verified-1!');
  await page.locator(`${LOGIN_FORM} button[type="submit"]`).click();
  await expect(page).toHaveURL(/\/mon-espace\/$/);
};

const loginAsEditor = async (page) => {
  await page.goto('/wp-login.php');
  const username = page.locator('#user_login');
  const password = page.locator('#user_pass');
  await expect(username).toBeFocused();
  await username.fill('e2e-legacy-editor-donor@example.test');
  await password.fill('E2e-Legacy-Editor-1!');
  await expect(username).toHaveValue('e2e-legacy-editor-donor@example.test');
  await expect(password).toHaveValue('E2e-Legacy-Editor-1!');
  await page.locator('#wp-submit').click();
  await expect(page).toHaveURL(/\/wp-admin\//);
};

test('enumerates the contact pattern through the real WordPress registry without Salesforce', async ({
  page, request, salesforceTestId,
}) => {
  await loginAsEditor(page);
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

test('redirects after one Case and refreshing the two rendered forms does not send again', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay);
  await resetSalesforceCalls(request, salesforceTestId);
  await gotoWithoutCookieOverlay(`${CONTACT_PAGE}&subject=${encodeURIComponent("Demande d'un reçu")}`);
  const forms = page.locator('form:has(#subject)');
  await expect(forms).toHaveCount(2);
  await expect(forms.first().locator('#subject')).toHaveValue("Demande d'un reçu");
  const getCalls = await getSalesforceCalls(request, salesforceTestId);
  expect(getCalls.filter((call) => call.url.includes('/sobjects/Contact/'))).toEqual([]);
  const message = 'Premier paragraphe\n\nDeuxième paragraphe';
  await forms.first().locator('#message').fill(message);
  await resetSalesforceCalls(request, salesforceTestId);
  const submitted = page.waitForResponse((response) => response.request().method() === 'POST' && response.url().includes('/mon-espace/e2e-contact-twice/'));

  await forms.first().getByRole('button', { name: 'Enregistrer' }).click();

  expect((await submitted).status()).toBe(303);
  await expect(page).toHaveURL(/\/mon-espace\/e2e-contact-twice\/\?contact_sent=1$/);
  await expect(page.getByText('Votre demande de contact a bien été prise en compte')).toHaveCount(2);
  await expect(page.getByRole('link', { name: 'Mes demandes', exact: true }).last()).toHaveAttribute('href', /\/mon-espace\/mes-dons\/mes-demandes\/$/);
  await page.reload();
  await expect(page.getByText('Votre demande de contact a bien été prise en compte')).toHaveCount(2);
  const calls = await getSalesforceCalls(request, salesforceTestId);
  const cases = calls.filter((call) => call.method === 'POST' && call.url.endsWith('/sobjects/Case'));
  expect(cases).toHaveLength(1);
  expect(JSON.parse(cases[0].body).Subject).toBe("Demande d'un reçu");
  // Browsers serialize textarea line endings as CRLF in a form POST.
  expect(JSON.parse(cases[0].body).Description.replace(/\r\n/g, '\n')).toBe(message);
});

test('rejects invalid POST values on the server before reading or creating Salesforce records', async ({
  page, request, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  await login(page, gotoWithoutCookieOverlay);
  await gotoWithoutCookieOverlay(CONTACT_PAGE);
  const nonce = await page.locator('[name="contact_nonce"]').first().inputValue();
  const invalidFields = [
    { subject: '', message: 'Message conservé' },
    { subject: 'é'.repeat(256), message: 'Message conservé' },
    { subject: 'Objet conservé', 'message[]': 'x' },
  ];
  for (const fields of invalidFields) {
    await resetSalesforceCalls(request, salesforceTestId);
    const response = await page.request.post(CONTACT_PAGE, {
      form: { aif_donor_contact_form: '1', contact_nonce: nonce, ...fields },
    });
    expect(response.status()).toBe(200);
    const body = await response.text();
    expect(body).toContain('Veuillez vérifier les champs du formulaire.');
    expect(body).toContain('aria-invalid="true"');
    expect(body).not.toContain('Votre demande de contact a bien été prise en compte');
    const calls = await getSalesforceCalls(request, salesforceTestId);
    expect(calls.filter((call) => call.method === 'POST' || call.url.includes('/sobjects/Contact/'))).toEqual([]);
  }
});

test('public REST content and search remain accessible without rendering a donor form', async ({
  page, request, salesforceTestId,
}) => {
  await resetSalesforceCalls(request, salesforceTestId);
  const response = await request.get('/wp-json/wp/v2/pages?slug=e2e-contact-twice', {
    headers: { 'X-AIF-E2E-Test-Id': salesforceTestId },
  });
  expect(response.status()).toBe(200);
  const pages = await response.json();
  expect(pages[0].content.rendered).not.toContain('contact_nonce');
  const search = await page.goto('/?s=Contact+donateur');
  expect(search.status()).toBe(200);
  await expect(page.locator('[name="contact_nonce"]')).toHaveCount(0);
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});

test('keeps anonymous and ineligible submissions behind the shared donor access checks', async ({
  page, request, context, gotoWithoutCookieOverlay, salesforceTestId,
}) => {
  const form = {
    aif_donor_contact_form: '1', contact_nonce: 'untrusted', subject: 'Objet', message: 'Message',
  };
  await resetSalesforceCalls(request, salesforceTestId);
  const anonymous = await request.post(CONTACT_PAGE, {
    form, maxRedirects: 0, headers: { 'X-AIF-E2E-Test-Id': salesforceTestId },
  });
  expect(anonymous.status()).toBe(302);
  expect(anonymous.headers().location).toContain('/connectez-vous/');
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);

  await login(page, gotoWithoutCookieOverlay);
  await context.setExtraHTTPHeaders({
    'X-AIF-E2E-Test-Id': salesforceTestId, 'X-AIF-E2E-Apex-Result': 'ineligible',
  });
  await resetSalesforceCalls(request, salesforceTestId);
  const ineligible = await page.request.post(CONTACT_PAGE, { form, maxRedirects: 0 });
  expect(ineligible.status()).toBe(302);
  expect(ineligible.headers().location).toContain('/connectez-vous/');
  const calls = await getSalesforceCalls(request, salesforceTestId);
  expect(calls.filter((call) => call.method === 'POST' || call.url.includes('/sobjects/Contact/'))).toEqual([]);
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
  await loginAsEditor(page);
  await resetSalesforceCalls(request, salesforceTestId);

  await gotoWithoutCookieOverlay(`${CONTACT_PAGE}&preview=true`);

  await expect(page.getByText('Le formulaire est disponible dans votre espace donateur.')).toHaveCount(2);
  await expect(page.locator('form:has(#subject)')).toHaveCount(0);
  expect(await getSalesforceCalls(request, salesforceTestId)).toEqual([]);
});

test('renders the registered donor contact preview inside the real iframe editor without Salesforce', async ({
  page, request, salesforceTestId,
}, testInfo) => {
  await loginAsEditor(page);
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
  page, request, salesforceTestId,
}, testInfo) => {
  await loginAsEditor(page);
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
      { name: 'GET page view content', method: 'GET', url: `/wp-json/wp/v2/pages/${pages[0].id}` },
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
