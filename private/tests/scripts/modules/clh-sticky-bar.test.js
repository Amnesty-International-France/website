import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import StickyBar from '../../../src/scripts/modules/clh-sticky-bar';

let bar;
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
      <div class="clh-band"></div>
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
  document.querySelector('.clh-timeline').getBoundingClientRect = () => ({
    top: stopTop,
  });
  document.querySelector('.clh-donation').getBoundingClientRect = () => ({
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
    const reminder = document.createElement('div');
    reminder.setAttribute('data-clh-reminder', '');
    document.querySelector('.clh-timeline').before(reminder);
    reminder.getBoundingClientRect = () => ({ top: stopTop });
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
    expect(document.querySelector('.has-clh-sticky-stop')).toBeNull();

    stopTop = -2000;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    stopTop = 300;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(true);
    expect(bar.inert).toBe(false);
  });

  it('recomputes the handover when the header or sticky height changes', () => {
    const reminder = document.createElement('div');
    reminder.setAttribute('data-clh-reminder', '');
    document.querySelector('.clh-timeline').before(reminder);
    reminder.getBoundingClientRect = () => ({ top: 220 });
    bannerBottom = -100;
    StickyBar();
    expect(bar.classList.contains('is-in')).toBe(true);
    headerBottom = 112;
    bar.getBoundingClientRect = () => ({ height: 120 });
    resize();
    vi.advanceTimersByTime(20);
    expect(bar.classList.contains('is-in')).toBe(false);
    expect(reminder.style.getPropertyValue('--clh-sticky-height')).toBe('');
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

  it('stays sticky while the timeline enters the viewport, then scrolls out with its top', () => {
    bannerBottom = -100;
    StickyBar();
    stopTop = 300;
    scroll();
    expect(bar.style.top).toBe('80px');
    expect(bar.classList.contains('is-in')).toBe(true);
    stopTop = 40;
    scroll();
    expect(bar.style.top).toBe('40px');
    stopTop = -200;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    stopTop = -2000;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    stopTop = 500;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(true);
  });

  it('updates the header offset and reserved space after a resize', () => {
    bannerBottom = -100;
    StickyBar();
    headerBottom = 108;
    bar.getBoundingClientRect = () => ({ height: 160 });
    resize();
    vi.advanceTimersByTime(20);
    expect(bar.style.top).toBe('108px');
    expect(
      document.querySelector('.clh-timeline').style.getPropertyValue('--clh-sticky-height'),
    ).toBe('160px');
  });

  it('stops at the donation when there are no testimonials', () => {
    document.querySelector('.clh-timeline').remove();
    bannerBottom = -100;
    StickyBar();
    expect(document.querySelector('.clh-donation').classList.contains('has-clh-sticky-stop')).toBe(
      true,
    );
    stopTop = -200;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
  });

  it('does nothing when incomplete final results leave no indicator', () => {
    bar.remove();
    expect(() => StickyBar()).not.toThrow();
    expect(document.querySelector('.has-clh-sticky-stop')).toBeNull();
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

  it('stops at sharing when testimonials and donation are absent', () => {
    document.querySelector('.clh-timeline').remove();
    document.querySelector('.clh-donation').remove();
    const share = document.querySelector('.clh-share');
    share.getBoundingClientRect = () => ({ top: stopTop });
    bannerBottom = -100;
    StickyBar();
    expect(share.classList.contains('has-clh-sticky-stop')).toBe(true);
    expect(bar.classList.contains('is-in')).toBe(true);
    stopTop = -200;
    scroll();
    expect(bar.classList.contains('is-in')).toBe(false);
    expect(bar.inert).toBe(true);
  });
});
