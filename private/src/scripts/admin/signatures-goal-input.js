const SELECTOR = '.acf-field[data-name="objectif_signatures"] input[type="number"]';

document.addEventListener(
  'wheel',
  event => {
    const input = event.target;

    if (input instanceof HTMLInputElement && input.matches(SELECTOR) && input === document.activeElement) {
      input.blur();
    }
  },
  { capture: true, passive: true },
);
