import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import Counters from '../../../src/scripts/modules/clh-counters';

let reveal;

beforeEach(() => {
  vi.useFakeTimers();
  vi.spyOn(performance, 'now').mockImplementation(() => Date.now());
  vi.stubGlobal('requestAnimationFrame', (callback) => setTimeout(() => callback(Date.now()), 16));
  vi.stubGlobal('matchMedia', () => ({ matches: false }));
  vi.stubGlobal(
    'IntersectionObserver',
    class {
      constructor(callback) {
        reveal = callback;
        this.observe = vi.fn();
        this.unobserve = vi.fn();
      }
    },
  );
  document.body.innerHTML = '<div data-clh-reveal><span data-clh-count></span></div>';
});

afterEach(() => {
  vi.restoreAllMocks();
  vi.unstubAllGlobals();
  vi.useRealTimers();
});

const start = (value) => {
  const output = document.querySelector('[data-clh-count]');
  output.textContent = value;
  Counters();
  reveal([{ isIntersecting: true, target: output.parentElement }]);
  return output;
};

describe('CLH counters', () => {
  it.each(['1,5 M', '7,5 %', '1.5 M', '1 234,5', '1,234.5', '2024 / 2025'])(
    'keeps %s unchanged throughout the reveal',
    (value) => {
      const output = start(value);
      vi.advanceTimersByTime(500);
      expect(output.textContent).toBe(value);
      vi.runAllTimers();
      expect(output.textContent).toBe(value);
      expect(output.style.width).toBe('');
    },
  );

  it.each(['12345', '12 345', '12\u00a0345', '12\u202f345', '67 %', '7 M'])(
    'animates %s and preserves its formatting at the end',
    (value) => {
      const output = start(value);
      vi.advanceTimersByTime(100);
      expect(output.textContent).not.toBe(value);
      vi.runAllTimers();
      expect(output.textContent).toBe(value);
      expect(output.style.width).toBe('');
    },
  );
});
