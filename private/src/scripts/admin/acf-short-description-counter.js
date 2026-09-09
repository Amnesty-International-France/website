const FIELD_SELECTOR = '.acf-field[data-name="short_description"] textarea';
const MAX_LENGTH = 1000;

const updateCounter = (counter, value) => {
  const currentCount = counter.querySelector('.amnesty-acf-character-count-current');

  if (!currentCount) {
    return;
  }

  currentCount.textContent = value.length;
  currentCount.classList.toggle('is-over-limit', value.length > MAX_LENGTH);
};

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
    const currentCount = document.createElement('span');
    container.className = 'amnesty-acf-character-count-wrapper';
    counter.className = 'amnesty-acf-character-count';
    currentCount.className = 'amnesty-acf-character-count-current';
    counter.setAttribute('aria-live', 'polite');

    textarea.removeAttribute('maxlength');
    textarea.setAttribute('data-character-counter-initialised', 'true');
    wrapper.insertBefore(container, textarea);
    container.appendChild(textarea);
    counter.appendChild(currentCount);
    counter.append(` / ${MAX_LENGTH} caractères`);
    container.appendChild(counter);

    updateCounter(counter, textarea.value);
    textarea.addEventListener('input', () => {
      updateCounter(counter, textarea.value);
    });
  });
};

document.addEventListener('DOMContentLoaded', initShortDescriptionCounter);

if (window.acf) {
  window.acf.addAction('append', initShortDescriptionCounter);
}
