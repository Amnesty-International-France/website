/**
 * Validation of a testimonial of the "Ils vous disent merci" block, entered in the editor.
 */

import { __, sprintf } from '@wordpress/i18n';

export const TESTIMONIAL_MAX_LENGTHS = {
  name: 60,
  country: 40,
  activity: 90,
  quote: 280,
  description: 400,
};

// Same bound as AMNESTY_CLH_FIRST_CAMPAIGN_YEAR in
// wp-content/themes/humanity-theme/includes/post-types/clh.php.
export const FIRST_TESTIMONIAL_YEAR = 2001;

const requiredMessage = () => __('Ce champ est obligatoire.', 'amnesty');

const isBlank = (value) => String(value ?? '').trim() === '';

export const remainingCharacters = (field, value) =>
  TESTIMONIAL_MAX_LENGTHS[field] - (value ?? '').length;

const validateYear = (year, currentYear) => {
  if (isBlank(year)) {
    return requiredMessage();
  }

  const text = String(year);
  const number = Number(text);

  if (!/^\d{4}$/.test(text) || number < FIRST_TESTIMONIAL_YEAR || number > currentYear) {
    return sprintf(
      /* translators: 1: first accepted year, 2: last accepted year */
      __('Saisissez une année comprise entre %1$d et %2$d.', 'amnesty'),
      FIRST_TESTIMONIAL_YEAR,
      currentYear,
    );
  }

  return null;
};

/**
 * @param {Object} testimonial the testimonial as stored in the block attribute
 * @param {number} currentYear the latest year allowed
 *
 * @return {Object<string, string>} an error message per invalid field, empty when valid
 */
export const validateTestimonial = (testimonial, currentYear) => {
  const errors = {};

  Object.entries(TESTIMONIAL_MAX_LENGTHS).forEach(([field, maxLength]) => {
    const value = testimonial[field] ?? '';

    if (isBlank(value)) {
      errors[field] = requiredMessage();
    } else if (value.length > maxLength) {
      /* translators: %d: maximum number of characters */
      errors[field] = sprintf(__('%d caractères maximum.', 'amnesty'), maxLength);
    }
  });

  const yearError = validateYear(testimonial.year, currentYear);

  if (yearError) {
    errors.year = yearError;
  }

  if (!(testimonial.imageId > 0)) {
    errors.imageId = __('Choisissez une photo.', 'amnesty');
  }

  return errors;
};
