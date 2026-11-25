import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

let exitPopin;
let STORAGE_KEY;
let listeners;
let showModal;

const dialog = () => document.querySelector('dialog.exit-popin');

const exitThroughTop = () => {
  document.dispatchEvent(new MouseEvent('mouseout', { clientY: 0, bubbles: true }));
};

const useTouchDevice = (coarse) => {
  window.matchMedia = vi.fn((query) => ({ matches: coarse && query === '(pointer: coarse)' }));
};

// Without the flag, only the module's own state keeps the popin from opening twice.
const blockStorage = () => {
  vi.spyOn(Storage.prototype, 'getItem').mockImplementation(() => {
    throw new Error('SecurityError');
  });
  vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
    throw new Error('SecurityError');
  });
};

const openOnDesktop = () => {
  exitPopin();
  exitThroughTop();
  vi.advanceTimersByTime(1000);
};

beforeEach(async () => {
  // Fresh module per test: its pending timers live in module-level state.
  vi.resetModules();
  ({ default: exitPopin, STORAGE_KEY } = await import('../../../src/scripts/modules/exit-popin'));
  vi.useFakeTimers();
  vi.setSystemTime(new Date('2026-11-25T12:00:00+01:00'));
  listeners = [vi.spyOn(window, 'addEventListener'), vi.spyOn(document, 'addEventListener')];
  showModal = vi.fn(function showModalStub() {
    this.setAttribute('open', '');
  });
  HTMLDialogElement.prototype.showModal = showModal;
  HTMLDialogElement.prototype.close = function closeStub() {
    this.removeAttribute('open');
    this.dispatchEvent(new Event('close'));
  };
  useTouchDevice(false);
  sessionStorage.clear();
  // Overlay tests toggle classes on <html> and <body>; a failed one must not leak them.
  document.documentElement.className = '';
  document.body.className = '';
  document.body.innerHTML = '<a href="/" class="page-link">Accueil</a>';
});

afterEach(() => {
  const [onWindow, onDocument] = listeners;
  onWindow.mock.calls.forEach(([type, callback, options]) =>
    window.removeEventListener(type, callback, options),
  );
  onDocument.mock.calls.forEach(([type, callback, options]) =>
    document.removeEventListener(type, callback, options),
  );
  delete HTMLDialogElement.prototype.showModal;
  delete HTMLDialogElement.prototype.close;
  delete window.matchMedia;
  vi.restoreAllMocks();
  vi.useRealTimers();
});

describe('exit popin desktop trigger', () => {
  it('opens 1 s after the cursor leaves through the top of the window', () => {
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(999);
    expect(dialog()).toBeNull();
    vi.advanceTimersByTime(1);
    expect(dialog().hasAttribute('open')).toBe(true);
    expect(showModal).toHaveBeenCalledTimes(1);
  });

  it('ignores the cursor leaving anywhere else', () => {
    exitPopin();
    document.dispatchEvent(new MouseEvent('mouseout', { clientY: 200, bubbles: true }));
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();
  });

  it('opens once, 1 s after the first exit, when the cursor leaves several times within the delay', () => {
    blockStorage();
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(500);
    exitThroughTop();
    vi.advanceTimersByTime(500);
    expect(dialog().hasAttribute('open')).toBe(true);
    vi.advanceTimersByTime(1000);
    expect(showModal).toHaveBeenCalledTimes(1);
    expect(document.querySelectorAll('dialog.exit-popin')).toHaveLength(1);
  });

  it('does not start the inactivity timer without a touch screen', () => {
    exitPopin();
    vi.advanceTimersByTime(60000);
    expect(dialog()).toBeNull();
  });
});

describe('exit popin touch trigger', () => {
  beforeEach(() => useTouchDevice(true));

  it('opens after 10 s of inactivity', () => {
    exitPopin();
    vi.advanceTimersByTime(9999);
    expect(dialog()).toBeNull();
    vi.advanceTimersByTime(1);
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it.each([
    ['touchstart', () => document.dispatchEvent(new Event('touchstart', { bubbles: true }))],
    ['scroll', () => window.dispatchEvent(new Event('scroll'))],
    // Scroll events from an inner scroller do not bubble: only a capture listener sees them.
    [
      'scroll inside an element',
      () => document.querySelector('.page-link').dispatchEvent(new Event('scroll')),
    ],
    ['keydown', () => document.dispatchEvent(new KeyboardEvent('keydown', { bubbles: true }))],
  ])('restarts the 10 s on %s', (_, interact) => {
    exitPopin();
    vi.advanceTimersByTime(9000);
    interact();
    vi.advanceTimersByTime(9999);
    expect(dialog()).toBeNull();
    vi.advanceTimersByTime(1);
    expect(dialog().hasAttribute('open')).toBe(true);
  });
});

describe('exit popin and other overlays', () => {
  it('skips an exit gesture while the urgent pop-in is shown, then opens on the next one', () => {
    document.body.insertAdjacentHTML('beforeend', '<div class="urgent-banner"></div>');
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();
    expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();

    document.querySelector('.urgent-banner').classList.add('hidden');
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it.each([
    ['the cookie banner', 'onetrust-banner-sdk'],
    ['the cookie preference center', 'onetrust-pc-sdk'],
  ])('skips an exit gesture while %s is shown', (_, id) => {
    document.body.insertAdjacentHTML('beforeend', `<div id="${id}"></div>`);
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();

    document.getElementById(id).style.display = 'none';
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it.each([
    [
      'the search overlay',
      '<div class="jetpack-instant-search__overlay"></div>',
      'is-hidden',
      'add',
    ],
    [
      'the newsletter confirmation',
      '<div id="confirmationPopup" class="popup popup-visible"></div>',
      'popup-visible',
      'remove',
    ],
  ])('waits while %s is shown', (_, markup, toggledClass, method) => {
    document.body.insertAdjacentHTML('beforeend', markup);
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();

    document.body.lastElementChild.classList[method](toggledClass);
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it.each([
    ['the mobile menu', document.documentElement, 'mobile-menu-open'],
    ['the My Space menu', document.body, 'menu-open'],
  ])('waits while %s is open', (_, element, openClass) => {
    useTouchDevice(true);
    element.classList.add(openClass);
    exitPopin();
    vi.advanceTimersByTime(10000);
    expect(dialog()).toBeNull();

    element.classList.remove(openClass);
    vi.advanceTimersByTime(10000);
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it('restarts the inactivity timer when an overlay is shown at the end of it', () => {
    useTouchDevice(true);
    document.body.insertAdjacentHTML('beforeend', '<div id="onetrust-banner-sdk"></div>');
    exitPopin();
    vi.advanceTimersByTime(10000);
    expect(dialog()).toBeNull();
    expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();

    document.getElementById('onetrust-banner-sdk').style.visibility = 'hidden';
    vi.advanceTimersByTime(10000);
    expect(dialog().hasAttribute('open')).toBe(true);
  });
});

describe('exit popin closing', () => {
  it.each([
    ['the close button', () => dialog().querySelector('.exit-popin-close')],
    ['the backdrop', () => dialog()],
    ['the CTA', () => dialog().querySelector('.exit-popin-cta')],
  ])('closes on a click on %s', (_, target) => {
    openOnDesktop();
    target().dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
    expect(dialog()).toBeNull();
  });

  it('stays open on a click inside the popin', () => {
    openOnDesktop();
    dialog().querySelector('.exit-popin-text').click();
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it('moves focus into the popin, then back where it was', () => {
    const link = document.querySelector('.page-link');
    link.focus();
    openOnDesktop();
    expect(document.activeElement).toBe(dialog().querySelector('.exit-popin-close'));

    dialog().querySelector('.exit-popin-close').click();
    expect(document.activeElement).toBe(link);
  });
});

describe('exit popin frequency', () => {
  it('remembers it was shown as soon as it opens', () => {
    openOnDesktop();
    expect(sessionStorage.getItem(STORAGE_KEY)).not.toBeNull();
  });

  it('does not open again in the same tab', () => {
    sessionStorage.setItem(STORAGE_KEY, 'true');
    useTouchDevice(true);
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(60000);
    expect(dialog()).toBeNull();
  });

  it('does not open again after being closed on the same page, even without storage', () => {
    blockStorage();
    openOnDesktop();
    dialog().querySelector('.exit-popin-close').click();
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();
    expect(showModal).toHaveBeenCalledTimes(1);
  });

  it('still opens when the session storage is unavailable', () => {
    blockStorage();
    openOnDesktop();
    expect(dialog().hasAttribute('open')).toBe(true);
  });

  it('stops its triggers when the flag appears after the page loaded', () => {
    useTouchDevice(true);
    exitPopin();
    sessionStorage.setItem(STORAGE_KEY, 'true');
    vi.advanceTimersByTime(10000);
    expect(dialog()).toBeNull();
    expect(vi.getTimerCount()).toBe(0);
  });

  it('does not run on a browser without <dialog>', () => {
    delete HTMLDialogElement.prototype.showModal;
    exitPopin();
    exitThroughTop();
    vi.advanceTimersByTime(1000);
    expect(dialog()).toBeNull();
    expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();
  });
});

describe('exit popin countdown refresh', () => {
  it('refreshes every minute and switches text when the deadline passes', () => {
    vi.setSystemTime(new Date('2026-12-31T23:58:59+01:00'));
    openOnDesktop();
    expect(dialog().querySelector('.exit-popin-countdown').textContent).toMatch(/1\s+minute\b/);

    vi.advanceTimersByTime(59999);
    expect(dialog().querySelector('.exit-popin-countdown').textContent).toMatch(/1\s+minute\b/);
    vi.advanceTimersByTime(1);
    expect(dialog().querySelector('.exit-popin-countdown')).toBeNull();
    expect(dialog().querySelector('.exit-popin-cta-label').textContent).toBe('Je fais un don');
  });

  it('stops refreshing once closed', () => {
    openOnDesktop();
    const whileOpen = vi.getTimerCount();
    dialog().querySelector('.exit-popin-close').click();
    expect(vi.getTimerCount()).toBe(whileOpen - 1);
  });
});
