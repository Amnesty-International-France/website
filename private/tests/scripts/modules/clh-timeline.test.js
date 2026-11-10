import { beforeEach, describe, expect, it, vi } from 'vitest';
import Timeline from '../../../src/scripts/modules/clh-timeline';

const fixture = (id) => `
<div class="clh-timeline-dial">
  <div class="clh-timeline-year is-empty"><span class="clh-timeline-mark">2020</span></div>
  ${[2022, 2024]
    .map(
      (year) => `
    <div class="clh-timeline-year">
      <label class="clh-timeline-mark"><input class="clh-timeline-input" type="radio" name="${id}-year" value="${year}" ${year === 2024 ? 'checked' : ''}></label>
      <div class="clh-timeline-panel">
        ${Array.from(
          { length: 6 },
          (_, rank) => `
          <input class="clh-timeline-pick-input" type="radio" name="${id}-${year}" value="${rank}" ${rank === 0 ? 'checked' : ''}>
          <article class="clh-timeline-slide${rank === 0 ? ' is-active' : ''}">Personne ${rank}</article>
        `,
        ).join('')}
      </div>
    </div>`,
    )
    .join('')}
</div>`;

const change = (input) => {
  input.click();
};

describe('CLH timeline', () => {
  beforeEach(() => {
    vi.stubGlobal(
      'matchMedia',
      vi.fn(() => ({ matches: false })),
    );
    Element.prototype.scrollTo = vi.fn();
    document.body.innerHTML = fixture('one') + fixture('two');
    Timeline();
  });

  it('initializes and centers every independent block', () => {
    expect(Element.prototype.scrollTo).toHaveBeenCalledTimes(2);
    expect(document.querySelectorAll('.clh-timeline-input:checked')).toHaveLength(2);
  });

  it('can select the sixth person without changing another block', () => {
    const dials = document.querySelectorAll('.clh-timeline-dial');
    const panel = dials[0]
      .querySelector('.clh-timeline-input:checked')
      .closest('.clh-timeline-year')
      .querySelector('.clh-timeline-panel');
    change(panel.querySelector('[value="5"]'));
    expect(panel.querySelector('.is-active').textContent).toBe('Personne 5');
    expect(dials[1].querySelector('.is-active').textContent).toBe('Personne 0');
  });

  it('resets to the first person when returning to a year and updates the watermark', () => {
    const dial = document.querySelector('.clh-timeline-dial');
    const latest = dial.querySelector('[value="2024"]');
    const panel = latest.closest('.clh-timeline-year').querySelector('.clh-timeline-panel');
    change(panel.querySelector('[value="5"]'));
    change(dial.querySelector('[value="2022"]'));
    expect(dial.style.getPropertyValue('--clh-timeline-cursor')).toBe('1');
    change(latest);
    expect(panel.querySelector('.is-active').textContent).toBe('Personne 0');
    expect(panel.querySelector('[value="0"]').checked).toBe(true);
    expect(dial.style.getPropertyValue('--clh-timeline-cursor')).toBe('2');
    expect(Element.prototype.scrollTo).toHaveBeenLastCalledWith(
      expect.objectContaining({ behavior: 'smooth' }),
    );
  });

  it('respects reduced motion when navigating the years', () => {
    vi.stubGlobal(
      'matchMedia',
      vi.fn(() => ({ matches: true })),
    );
    document.body.innerHTML = fixture('reduced');
    Timeline();
    change(document.querySelector('[value="2022"]'));
    expect(Element.prototype.scrollTo).toHaveBeenLastCalledWith(
      expect.objectContaining({ behavior: 'instant' }),
    );
  });
});
