import { buildPopin, updatePopin } from './exit-popin-content';

// Set when the popin opens: it opens at most once per browser tab.
export const STORAGE_KEY = 'aif_exit_popin_shown';

const OPEN_DELAY = 1000;
const EXIT_MARGIN = 5;
const INACTIVITY_DELAY = 10 * 1000;
const REFRESH_INTERVAL = 60 * 1000;
const ACTIVITY_EVENTS = ['touchstart', 'scroll', 'keydown'];

const SELECTORS = {
  urgentBanner: '.urgent-banner:not(.hidden)',
  // Set on <html> by overlays.js while the burger menu is open.
  mobileMenu: 'html.mobile-menu-open',
  // Set on <body> by my-space-mobile-menu.js while the My Space drawer is open.
  mySpaceMenu: 'body.menu-open',
  // Jetpack Instant Search keeps its overlay in the page, hidden with this class.
  searchOverlay: '.jetpack-instant-search__overlay:not(.is-hidden)',
  // Newsletter sign-up confirmation, rendered open by footer-content.php.
  newsletterPopup: '#confirmationPopup.popup-visible',
  cookieBanner: '#onetrust-banner-sdk',
  // OneTrust cookie settings panel, which can open without the banner.
  cookiePreferences: '#onetrust-pc-sdk',
};

const state = {
  openTimer: null,
  inactivityTimer: null,
  // Set by exitPopin(): removes the triggers once the popin opens.
  stopTriggers: () => {},
};

const wasShown = () => {
  try {
    return window.sessionStorage.getItem(STORAGE_KEY) !== null;
  } catch {
    return false;
  }
};

const markShown = () => {
  try {
    window.sessionStorage.setItem(STORAGE_KEY, 'true');
  } catch {
    // Storage blocked: the popin may open again on the next page.
  }
};

const isShown = (element) => {
  if (!element) return false;
  const style = window.getComputedStyle(element);
  return style.display !== 'none' && style.visibility !== 'hidden';
};

const isOverlayShown = () =>
  Boolean(
    document.querySelector(
      [
        SELECTORS.urgentBanner,
        SELECTORS.mobileMenu,
        SELECTORS.mySpaceMenu,
        SELECTORS.searchOverlay,
        SELECTORS.newsletterPopup,
      ].join(', '),
    ),
  ) ||
  isShown(document.querySelector(SELECTORS.cookieBanner)) ||
  isShown(document.querySelector(SELECTORS.cookiePreferences));

const openPopin = () => {
  const previousFocus = document.activeElement;
  const dialog = buildPopin(new Date());
  const refresh = setInterval(() => updatePopin(dialog, new Date()), REFRESH_INTERVAL);

  dialog.addEventListener('click', (event) => {
    const closer = event.target.closest('.exit-popin-close, .exit-popin-cta');
    if (event.target === dialog || closer) dialog.close();
  });
  dialog.addEventListener('close', () => {
    clearInterval(refresh);
    dialog.remove();
    previousFocus?.focus?.();
  });

  markShown();
  document.body.append(dialog);
  dialog.showModal();
  dialog.querySelector('.exit-popin-close').focus();
};

// Returns false only when an overlay is in the way, so the caller retries.
const tryOpen = () => {
  // Shown since this page loaded, e.g. in another page of the tab before a back navigation.
  if (wasShown()) {
    state.stopTriggers();
    return true;
  }
  if (isOverlayShown()) return false;
  state.stopTriggers();
  openPopin();
  return true;
};

// A null relatedTarget means the cursor left the document; a fast exit may
// report its last position a few pixels below the top edge.
const onMouseOut = (event) => {
  if (event.relatedTarget || event.clientY > EXIT_MARGIN || state.openTimer) return;
  if (wasShown() || isOverlayShown()) return;
  state.openTimer = setTimeout(() => {
    state.openTimer = null;
    tryOpen();
  }, OPEN_DELAY);
};

const restartInactivityTimer = () => {
  clearTimeout(state.inactivityTimer);
  state.inactivityTimer = setTimeout(() => {
    if (!tryOpen()) restartInactivityTimer();
  }, INACTIVITY_DELAY);
};

const stopTriggers = () => {
  document.removeEventListener('mouseout', onMouseOut);
  ACTIVITY_EVENTS.forEach((type) =>
    window.removeEventListener(type, restartInactivityTimer, { capture: true }),
  );
  clearTimeout(state.inactivityTimer);
  clearTimeout(state.openTimer);
  state.inactivityTimer = null;
  state.openTimer = null;
};

const exitPopin = () => {
  // Browsers without <dialog> (Safari before 15.4) do not get the popin.
  if (wasShown() || typeof window.HTMLDialogElement?.prototype.showModal !== 'function') return;

  state.stopTriggers = stopTriggers;
  // Only a mouse or a trackpad can fire it.
  document.addEventListener('mouseout', onMouseOut);

  if (window.matchMedia?.('(pointer: coarse)').matches) {
    ACTIVITY_EVENTS.forEach((type) =>
      window.addEventListener(type, restartInactivityTimer, { capture: true, passive: true }),
    );
    restartInactivityTimer();
  }
};

export default exitPopin;
