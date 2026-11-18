/**
 * Keeps the origin code of an incoming link (emailing, campaign...) along the visit.
 *
 * When a page is opened with `reserved_originecode` (or `code_origine`) in its URL,
 * the code is kept in the session and replaces the origin code of every link to
 * the donation site, so that the donation is attributed to the incoming link.
 */
export const STORAGE_KEY = 'aif_reserved_originecode';

const URL_PARAMS = ['reserved_originecode', 'code_origine'];
const DONATION_HOST = 'soutenir.amnesty.fr';
const DONATION_PARAM = 'reserved_originecode';
const CODE_PATTERN = /^[A-Za-z0-9_-]{1,50}$/;

const readCodeFromUrl = () => {
  const params = new URLSearchParams(window.location.search);

  const value = URL_PARAMS.map((name) => params.get(name)?.trim()).find(
    (candidate) => candidate && CODE_PATTERN.test(candidate),
  );

  return value ?? null;
};

const readStoredCode = () => {
  try {
    const value = window.sessionStorage.getItem(STORAGE_KEY);
    return value && CODE_PATTERN.test(value) ? value : null;
  } catch {
    return null;
  }
};

const storeCode = (code) => {
  try {
    window.sessionStorage.setItem(STORAGE_KEY, code);
  } catch {
    // Storage unavailable (private browsing, blocked): the code still applies to this page.
  }
};

const applyCodeToLink = (link, code) => {
  let url;
  try {
    url = new URL(link.href);
  } catch {
    return;
  }

  if (url.hostname !== DONATION_HOST || url.searchParams.get(DONATION_PARAM) === code) {
    return;
  }

  url.searchParams.set(DONATION_PARAM, code);
  // eslint-disable-next-line no-param-reassign -- the link is rewritten on purpose
  link.href = url.toString();
};

export const keepOriginCode = () => {
  const codeFromUrl = readCodeFromUrl();
  if (codeFromUrl) {
    storeCode(codeFromUrl);
  }

  const code = codeFromUrl ?? readStoredCode();
  if (!code) {
    return () => {};
  }

  document.querySelectorAll('a[href]').forEach((link) => applyCodeToLink(link, code));

  // Some links get their href updated after the page load (donation calculator):
  // the code is applied again just before the link is followed.
  const onLinkActivation = (event) => {
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (link) {
      applyCodeToLink(link, code);
    }
  };

  const events = ['click', 'auxclick', 'contextmenu'];
  events.forEach((type) => document.addEventListener(type, onLinkActivation, true));

  return () => {
    events.forEach((type) => document.removeEventListener(type, onLinkActivation, true));
  };
};
