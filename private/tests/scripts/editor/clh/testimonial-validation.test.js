import { describe, expect, it } from 'vitest';
import {
  FIRST_TESTIMONIAL_YEAR,
  TESTIMONIAL_MAX_LENGTHS,
  remainingCharacters,
  validateTestimonial,
} from '../../../../src/scripts/editor/clh/testimonial-validation';

const FIXED_CURRENT_YEAR = 2026;

const validTestimonial = () => ({
  year: 2020,
  name: 'Personne A',
  country: 'Pays A',
  activity: 'Défenseure des droits humains',
  quote: 'Merci.',
  description: 'Libérée après une mobilisation internationale.',
  imageId: 7,
});

const YEAR_MESSAGE = `Saisissez une année comprise entre 2001 et ${FIXED_CURRENT_YEAR}.`;

describe('TESTIMONIAL_MAX_LENGTHS', () => {
  it('sets the limit of each text field', () => {
    expect(TESTIMONIAL_MAX_LENGTHS).toEqual({
      name: 60,
      country: 40,
      activity: 90,
      quote: 280,
      description: 400,
    });
  });
});

describe('FIRST_TESTIMONIAL_YEAR', () => {
  it('is the first campaign year', () => {
    expect(FIRST_TESTIMONIAL_YEAR).toBe(2001);
  });
});

describe('remainingCharacters', () => {
  it('subtracts the value length from the field limit', () => {
    expect(remainingCharacters('name', 'Personne A')).toBe(50);
  });

  it('is 0 at the limit', () => {
    expect(remainingCharacters('country', 'a'.repeat(40))).toBe(0);
  });

  it('is the whole limit for a field not entered yet', () => {
    expect(remainingCharacters('name', undefined)).toBe(60);
  });
});

describe('validateTestimonial', () => {
  it('returns no error for a valid testimonial', () => {
    expect(validateTestimonial(validTestimonial(), FIXED_CURRENT_YEAR)).toEqual({});
  });

  it.each(['name', 'country', 'activity', 'quote', 'description'])('requires %s', (field) => {
    const testimonial = { ...validTestimonial(), [field]: '  ' };

    expect(validateTestimonial(testimonial, FIXED_CURRENT_YEAR)).toEqual({
      [field]: 'Ce champ est obligatoire.',
    });
  });

  it.each(
    Object.entries({
      name: 60,
      country: 40,
      activity: 90,
      quote: 280,
      description: 400,
    }),
  )('accepts %s at its limit and rejects it beyond', (field, limit) => {
    const atLimit = { ...validTestimonial(), [field]: 'a'.repeat(limit) };
    const beyondLimit = { ...validTestimonial(), [field]: 'a'.repeat(limit + 1) };

    expect(validateTestimonial(atLimit, FIXED_CURRENT_YEAR)).toEqual({});
    expect(validateTestimonial(beyondLimit, FIXED_CURRENT_YEAR)).toEqual({
      [field]: `${limit} caractères maximum.`,
    });
  });

  it.each([
    ['the first campaign year', 2001],
    ['the current year', FIXED_CURRENT_YEAR],
    ['a year written as text', '2020'],
  ])('accepts %s', (label, year) => {
    expect(validateTestimonial({ ...validTestimonial(), year }, FIXED_CURRENT_YEAR)).toEqual({});
  });

  it.each([
    ['a year before the first campaign', 2000, YEAR_MESSAGE],
    ['a year after the current year', FIXED_CURRENT_YEAR + 1, YEAR_MESSAGE],
    ['a year with a letter', '20a4', YEAR_MESSAGE],
    ['a year with 3 digits', '999', YEAR_MESSAGE],
    ['an empty year', '', 'Ce champ est obligatoire.'],
    ['a missing year', undefined, 'Ce champ est obligatoire.'],
  ])('rejects %s', (label, year, message) => {
    expect(validateTestimonial({ ...validTestimonial(), year }, FIXED_CURRENT_YEAR)).toEqual({
      year: message,
    });
  });

  it.each([
    ['missing', undefined],
    ['zero', 0],
  ])('requires a photo (%s)', (label, imageId) => {
    expect(validateTestimonial({ ...validTestimonial(), imageId }, FIXED_CURRENT_YEAR)).toEqual({
      imageId: 'Choisissez une photo.',
    });
  });

  it('reports every invalid field', () => {
    expect(Object.keys(validateTestimonial({}, FIXED_CURRENT_YEAR)).sort()).toEqual([
      'activity',
      'country',
      'description',
      'imageId',
      'name',
      'quote',
      'year',
    ]);
  });
});
