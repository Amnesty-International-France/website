import { beforeEach, describe, expect, it } from 'vitest';
import Presentation from '../../../src/scripts/modules/clh-presentation';

beforeEach(() => {
  document.body.innerHTML = `
    <div class="clh-editable"><div class="clh-pictos"><div class="wp-block-column">
      <figure class="wp-block-image"><img src="/picto.png" alt=""></figure>
      <p>Votre signature a du pouvoir.</p>
    </div></div></div>
    <figure class="wp-block-image"><img src="/outside.png" alt=""></figure>`;
});

describe('CLH presentation illustrations', () => {
  it('hides a missing illustration but preserves its phrase and unrelated images', () => {
    Presentation();
    document.querySelectorAll('img').forEach((image) => image.dispatchEvent(new Event('error')));
    expect(document.querySelector('.clh-pictos figure').hidden).toBe(true);
    expect(document.querySelector('.clh-pictos p').textContent).toBe(
      'Votre signature a du pouvoir.',
    );
    expect(document.querySelector('body > figure').hidden).toBe(false);
  });

  it('handles an image that failed before initialization', () => {
    const image = document.querySelector('img');
    Object.defineProperty(image, 'complete', { value: true });
    Object.defineProperty(image, 'naturalWidth', { value: 0 });
    Presentation();
    expect(image.closest('figure').hidden).toBe(true);
  });

  it('keeps loaded images and images that are still loading visible', () => {
    const image = document.querySelector('img');
    Presentation();
    expect(image.closest('figure').hidden).toBe(false);
    Object.defineProperty(image, 'complete', { value: true });
    Object.defineProperty(image, 'naturalWidth', { value: 120 });
    Presentation();
    expect(image.closest('figure').hidden).toBe(false);
  });
});
