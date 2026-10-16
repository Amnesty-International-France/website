import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import StickyBar from '../../../src/scripts/modules/clh-sticky-bar';

let bar;
let reminder;
let bannerBottom;
let stopTop;
let headerBottom;
let resize;
let events;

const scroll = () => {
  window.dispatchEvent(new Event('scroll'));
  vi.advanceTimersByTime(20);
};

beforeEach(() => {
  vi.useFakeTimers();
  events = vi.spyOn(window, 'addEventListener');
  vi.stubGlobal('requestAnimationFrame', (callback) => setTimeout(callback, 16));
  vi.stubGlobal(
    'ResizeObserver',
    class {
      constructor(callback) {
        resize = callback;
        this.observe = vi.fn();
      }
    },
  );
  vi.stubGlobal('MutationObserver', window.ResizeObserver);
  document.body.innerHTML = `
    <header class="page-header"></header>
    <main class="page-change-their-history">
      <div data-clh-sticky-bar hidden></div>
      <div class="clh-band" data-clh-reveal></div>
      <div class="clh-band" data-clh-reminder></div>
      <section class="clh-timeline"></section>
      <section class="clh-donation"></section>
      <div class="clh-share"></div>
    </main>`;
  bannerBottom = 500;
  stopTop = 2000;
  headerBottom = 80;
  bar = document.querySelector('[data-clh-sticky-bar]');
  bar.getBoundingClientRect = () => ({ height: 100 });
  document.querySelector('.page-header').getBoundingClientRect = () => ({
    bottom: headerBottom,
  });
  document.querySelector('.clh-band').getBoundingClientRect = () => ({
    bottom: bannerBottom,
  });
  reminder = document.querySelector('[data-clh-reminder]');
  reminder.getBoundingClientRect = () => ({
    top: stopTop,
  });
});

afterEach(() => {
  events.mock.calls.forEach(([type, callback]) => window.removeEventListener(type, callback));
  vi.restoreAllMocks();
  vi.unstubAllGlobals();
  vi.useRealTimers();
});

describe('CLH sticky indicator', () => {
  it('hands over to the in-flow reminder without overlap, then returns on upward scroll', () => {
    bannerBottom = -100;
    StickyBar();

    stopTop = 181;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(true);
    expect(bar.style.top).toBe('80px');
    stopTop = 180;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    expect(bar.inert).toBe(true);
    expect(reminder.hidden).toBe(false);
    expect(reminder.inert).not.toBe(true);

    stopTop = -2000;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    stopTop = 300;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(true);
    expect(bar.inert).toBe(false);
  });

  it('recomputes the handover when the header or sticky height changes', () => {
    reminder.getBoundingClientRect = () => ({ top: 220 });
    bannerBottom = -100;
    StickyBar();
    expect(bar.classList.contains('is-in')).toBe(true);
    headerBottom = 112;
    bar.getBoundingClientRect = () => ({ height: 120 });
    resize();
    vi.advanceTimersByTime(20);
    expect(bar.classList.contains('is-in')).toBe(false);
  });

  it('takes over below the header only after the banner leaves, then returns to the banner', () => {
    StickyBar();
    expect(bar.classList.contains('is-in')).toBe(false);
    expect(bar.inert).toBe(true);
    bannerBottom = 70;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(true);
    expect(bar.style.top).toBe('80px');
    bannerBottom = 500;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
  });

  it('keeps the sticky hidden when the reminder is missing', () => {
    reminder.remove();
    bannerBottom = -100;
    StickyBar();
    expect(bar.hidden).toBe(true);
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
  });

  it('does not mistake the reminder for a missing initial band', () => {
    document.querySelector('[data-clh-reveal]').remove();
    StickyBar();
    expect(bar.hidden).toBe(true);
  });

  it('identifies the initial band even when the reminder comes first in the DOM', () => {
    document.querySelector('[data-clh-reveal]').before(reminder);
    reminder.getBoundingClientRect = () => ({ top: stopTop, bottom: stopTop + 100 });
    bannerBottom = -100;
    StickyBar();
    expect(bar.classList.contains('is-in')).toBe(true);
    stopTop = 180;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
  });

  it('does nothing when incomplete final results leave no indicator', () => {
    bar.remove();
    expect(() => StickyBar()).not.toThrow();
  });

  it('stays below the admin bar when the site header scrolls out', () => {
    const adminBar = document.createElement('div');
    adminBar.id = 'wpadminbar';
    adminBar.getBoundingClientRect = () => ({ bottom: 32 });
    document.body.prepend(adminBar);
    bannerBottom = -100;
    headerBottom = -80;
    StickyBar();
    expect(bar.style.top).toBe('32px');
    expect(bar.inert).toBe(false);
    headerBottom = 112;
    scroll();
    expect(bar.style.top).toBe('112px');
  });
});
