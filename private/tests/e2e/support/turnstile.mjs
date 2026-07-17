export const TEST_SITE_KEY = '1x00000000000000000000BB';

const TURNSTILE_API_PATTERN = 'https://challenges.cloudflare.com/turnstile/v0/api.js*';

const mockScript = ({ mode, delay, token, trigger }) => `
(() => {
  const mode = ${JSON.stringify(mode)};
  const delay = ${Number(delay)};
  const token = ${JSON.stringify(token)};
  const trigger = ${JSON.stringify(trigger)};

  const addResponse = (form) => {
    let input = form.querySelector('[name="cf-turnstile-response"]');

    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'cf-turnstile-response';
      form.appendChild(input);
    }

    input.value = token;
  };

  const resolve = (widget) => {
    const form = widget.closest('form');

    if (mode === 'success' && form) {
      addResponse(form);
      callback(widget.dataset.callback, token);
      return;
    }

    if (mode === 'failure') {
      callback(widget.dataset.errorCallback);
    }
  };

  const callback = (name, value) => {
    if (name && typeof window[name] === 'function') {
      window[name](value);
    }
  };

  const render = () => {
    document.querySelectorAll('.cf-turnstile').forEach((widget) => {
      if (widget.dataset.mockTurnstileRendered === 'true') return;

      widget.dataset.mockTurnstileRendered = 'true';

      if (trigger === 'submit') {
        const form = widget.closest('form');

        if (!form) {
          return;
        }

        form.addEventListener('submit', () => {
          window.setTimeout(() => resolve(widget), delay);
        }, { capture: true, once: true });
        return;
      }

      window.setTimeout(() => resolve(widget), delay);
    });
  };

  window.turnstile = {
    render,
    reset: () => {},
    remove: () => {},
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', render);
  } else {
    render();
  }
})();
`;

const routeTurnstileApi = async (page, options) => {
  await page.route(TURNSTILE_API_PATTERN, async (route) => {
    await route.fulfill({
      contentType: 'application/javascript',
      body: mockScript(options),
    });
  });
};

export const mockSuccessfulTurnstile = async (
  page,
  { delay = 1000, token = 'mock-turnstile-token', trigger = 'render' } = {},
) => routeTurnstileApi(page, { mode: 'success', delay, token, trigger });

export const mockFailingTurnstile = async (page, { delay = 1000, trigger = 'render' } = {}) =>
  routeTurnstileApi(page, { mode: 'failure', delay, token: '', trigger });

export const mockSilentTurnstile = async (page) =>
  routeTurnstileApi(page, { mode: 'silent', delay: 0, token: '', trigger: 'render' });

export const expectTurnstileScriptsInHead = async (page, expect) => {
  const scriptOrder = await page
    .locator('head script[src]')
    .evaluateAll((scripts) => scripts.map((script) => script.getAttribute('src')));

  const localGuardIndex = scriptOrder.findIndex((src) =>
    src.includes('/assets/scripts/turnstile.js'),
  );
  const cloudflareIndex = scriptOrder.findIndex((src) =>
    src.includes('https://challenges.cloudflare.com/turnstile/v0/api.js'),
  );

  expect(localGuardIndex).toBeGreaterThanOrEqual(0);
  expect(cloudflareIndex).toBeGreaterThanOrEqual(0);
  expect(localGuardIndex).toBeLessThan(cloudflareIndex);
};

export const expectWidgetSiteKey = async (page, expect, expectedSiteKey = TEST_SITE_KEY) => {
  const widgets = page.locator('.cf-turnstile');

  await expect(widgets.first()).toHaveAttribute('data-sitekey', expectedSiteKey);
};

// Simulates aif-e2e-support.php's server-side Turnstile verification result
// (see its pre_http_request mock for challenges.cloudflare.com/.../siteverify)
// by appending the hidden inputs it reads from $_REQUEST, directly on the
// form being tested - regardless of which spec/form it's called from.
export const setServerSideTurnstileResult = async (
  page,
  formSelector,
  { success, error = 'invalid-input-response' },
) => {
  await page.locator(formSelector).evaluate(
    (form, options) => {
      const appendHiddenInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      };

      appendHiddenInput('aif_e2e_turnstile_verify_success', options.success ? '1' : '0');

      if (!options.success) {
        appendHiddenInput('aif_e2e_turnstile_verify_error', options.error);
      }
    },
    { success, error },
  );
};
