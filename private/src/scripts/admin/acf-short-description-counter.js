const FIELD_SELECTOR = '.acf-field[data-name="short_description"] textarea';
const MAX_LENGTH = 1000;

const getCounterText = (value) => `${value.length} / ${MAX_LENGTH} caractères`;

const initShortDescriptionCounter = () => {
  const textareas = document.querySelectorAll(FIELD_SELECTOR);

  if (!textareas.length) {
    return;
  }

  textareas.forEach((textarea) => {
    if (textarea.dataset.characterCounterInitialised === 'true') {
      return;
    }

    const wrapper = textarea.parentElement;

    if (!wrapper) {
      return;
    }

    const container = document.createElement('div');
    const counter = document.createElement('span');
    container.className = 'amnesty-acf-character-count-wrapper';
    counter.className = 'amnesty-acf-character-count';
    counter.setAttribute('aria-live', 'polite');

    textarea.setAttribute('maxlength', MAX_LENGTH);
    textarea.setAttribute('data-character-counter-initialised', 'true');
    wrapper.insertBefore(container, textarea);
    container.appendChild(textarea);
    container.appendChild(counter);

    counter.textContent = getCounterText(textarea.value);
    textarea.addEventListener('input', () => {
      counter.textContent = getCounterText(textarea.value);
    });
  });
};

document.addEventListener('DOMContentLoaded', initShortDescriptionCounter);

if (window.acf) {
  window.acf.addAction('append', initShortDescriptionCounter);
}
