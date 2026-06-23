const FIELD_SELECTOR = '#acf-field_6a155b0a8709c';
const MAX_LENGTH = 1000;

const getCounterText = (value) => `${value.length} / ${MAX_LENGTH} caractères`;

const initShortDescriptionCounter = () => {
  const textarea = document.querySelector(FIELD_SELECTOR);

  if (!textarea || textarea.dataset.characterCounterInitialised === 'true') {
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

  textarea.maxLength = MAX_LENGTH;
  textarea.dataset.characterCounterInitialised = 'true';
  wrapper.insertBefore(container, textarea);
  container.appendChild(textarea);
  container.appendChild(counter);

  counter.textContent = getCounterText(textarea.value);
  textarea.addEventListener('input', () => {
    counter.textContent = getCounterText(textarea.value);
  });
};

document.addEventListener('DOMContentLoaded', initShortDescriptionCounter);

if (window.acf) {
  window.acf.addAction('append', initShortDescriptionCounter);
}
