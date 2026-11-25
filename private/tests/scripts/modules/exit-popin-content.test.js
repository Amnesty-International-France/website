import { describe, expect, it } from 'vitest';
import {
  CTA_URL,
  DEADLINE,
  buildPopin,
  getRemaining,
  updatePopin,
} from '../../../src/scripts/modules/exit-popin-content';

const BEFORE = new Date('2026-11-25T12:00:00+01:00');
const AFTER = new Date('2027-01-01T00:00:00+01:00');

const paragraphs = (dialog) =>
  [...dialog.querySelectorAll('.exit-popin-text p')].map((p) => p.textContent.trim());

const ctaLabel = (dialog) => dialog.querySelector('.exit-popin-cta-label').textContent;

const expectNewTabLink = (cta) => {
  expect(cta.getAttribute('href')).toBe(CTA_URL);
  expect(cta.getAttribute('target')).toBe('_blank');
  expect(cta.getAttribute('rel')).toContain('noopener');
  expect(cta.querySelector('.screen-reader-text').textContent).toBe(' (nouvel onglet)');
};

const countdownItems = (dialog) =>
  [...dialog.querySelectorAll('.exit-popin-countdown-item')].map((item) =>
    item.textContent.replace(/\s+/g, ' ').trim(),
  );

describe('exit popin countdown', () => {
  it('ends on 1 January 2027 00:00 Paris time, whatever the machine time zone', () => {
    expect(DEADLINE.getTime()).toBe(Date.UTC(2026, 11, 31, 23));
  });

  it('counts down to 1 January 00:00 Paris time', () => {
    expect(getRemaining(BEFORE)).toEqual({ days: 36, hours: 12, minutes: 0 });
  });

  it('uses the Paris offset, not the visitor time zone', () => {
    expect(getRemaining(new Date('2026-12-31T22:59:00Z'))).toEqual({
      days: 0,
      hours: 0,
      minutes: 1,
    });
  });

  it('returns nothing from the deadline on', () => {
    expect(getRemaining(new Date('2026-12-31T23:00:00Z'))).toBeNull();
    expect(getRemaining(new Date('2027-03-01T10:00:00+01:00'))).toBeNull();
  });
});

describe('exit popin content before the deadline', () => {
  const dialog = buildPopin(BEFORE);

  it('is a dialog labelled by its title', () => {
    expect(dialog.tagName).toBe('DIALOG');
    const title = dialog.querySelector('h2');
    expect(title.id).not.toBe('');
    expect(dialog.getAttribute('aria-labelledby')).toBe(title.id);
    expect(title.textContent.trim()).toBe('PSSST... AVANT DE PARTIR !');
  });

  it('is described by its text', () => {
    const text = dialog.querySelector('.exit-popin-text');
    expect(text.id).not.toBe('');
    expect(dialog.getAttribute('aria-describedby')).toBe(text.id);
  });

  it('writes no empty class attribute', () => {
    expect(dialog.querySelectorAll('[class=""]')).toHaveLength(0);
  });

  it('has a close button with an accessible name', () => {
    const close = dialog.querySelector('.exit-popin-close');
    expect(close.getAttribute('type')).toBe('button');
    expect(close.getAttribute('aria-label')).toBe('Fermer');
  });

  it('shows the tax-reduction appeal', () => {
    expect(paragraphs(dialog)).toEqual([
      'Nous refusons tout financement de gouvernement et d’entreprise qui pourrait compromettre notre impartialité et notre indépendance.',
      'Pour garantir le respect des droits humains et préserver notre liberté d’action, nous dépendons du soutien de nos donatrices et donateurs.',
      'Faites un don au plus tard le 31 décembre et bénéficier d’une réduction d’impôt.',
    ]);
  });

  it('shows the countdown', () => {
    expect(countdownItems(dialog)).toEqual(['36 jours', '12 heures', '0 minutes']);
  });

  it('links the CTA to the donation form, in a new tab', () => {
    expectNewTabLink(dialog.querySelector('.exit-popin-cta'));
    expect(ctaLabel(dialog)).toBe('JE FAIS UN DON');
  });
});

describe('exit popin content from the deadline on', () => {
  const dialog = buildPopin(AFTER);

  it('keeps the title', () => {
    expect(dialog.querySelector('h2').textContent.trim()).toBe('PSSST... AVANT DE PARTIR !');
  });

  it('shows the generic appeal in lowercase, broken after France!', () => {
    expect(paragraphs(dialog)).toEqual([
      'Faites un don à Amnesty International France!',
      'Vous dénoncez les violations des droits humains et agissez concrètement pour protéger celles et ceux qui en sont victimes.',
    ]);
  });

  it('has no countdown', () => {
    expect(dialog.querySelector('.exit-popin-countdown')).toBeNull();
  });

  it('keeps the same CTA link with its own label', () => {
    expectNewTabLink(dialog.querySelector('.exit-popin-cta'));
    expect(ctaLabel(dialog)).toBe('Je fais un don');
  });
});

describe('exit popin refresh', () => {
  it('uses singular labels for 1', () => {
    const dialog = buildPopin(new Date('2026-12-30T22:58:00+01:00'));
    expect(countdownItems(dialog)).toEqual(['1 jour', '1 heure', '2 minutes']);
    updatePopin(dialog, new Date('2026-12-30T22:59:00+01:00'));
    expect(countdownItems(dialog)).toEqual(['1 jour', '1 heure', '1 minute']);
  });

  it('keeps the text and countdown nodes in place before the deadline', () => {
    const dialog = buildPopin(new Date('2026-12-30T22:58:00+01:00'));
    const firstParagraph = dialog.querySelector('.exit-popin-text p');
    const countdown = dialog.querySelector('.exit-popin-countdown');
    updatePopin(dialog, new Date('2026-12-30T22:59:00+01:00'));
    expect(dialog.querySelector('.exit-popin-text p')).toBe(firstParagraph);
    expect(dialog.querySelector('.exit-popin-countdown')).toBe(countdown);
  });

  it('switches to the generic appeal when the deadline passes', () => {
    const dialog = buildPopin(new Date('2026-12-31T23:59:00+01:00'));
    updatePopin(dialog, AFTER);
    expect(dialog.querySelector('.exit-popin-countdown')).toBeNull();
    expect(paragraphs(dialog)[0]).toBe('Faites un don à Amnesty International France!');
    expect(ctaLabel(dialog)).toBe('Je fais un don');
  });
});
