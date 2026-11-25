// End of the tax-reduction appeal: 1 January 2027 00:00, Europe/Paris.
export const DEADLINE = new Date('2027-01-01T00:00:00+01:00');

export const CTA_URL =
  'https://soutenir.amnesty.fr/b?cid=442&lang=fr_FR&reserved_originecode=26PFAWSI01';

const TITLE = 'PSSST... AVANT DE PARTIR !';
const TITLE_ID = 'exit-popin-title';
const TEXT_ID = 'exit-popin-text';

const MINUTE = 60 * 1000;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;

const BEFORE_DEADLINE = {
  paragraphs: [
    'Nous refusons tout financement de gouvernement et d’entreprise qui pourrait compromettre notre impartialité et notre indépendance.',
    'Pour garantir le respect des droits humains et préserver notre liberté d’action, nous dépendons du soutien de nos donatrices et donateurs.',
    'Faites un don au plus tard le 31 décembre et bénéficier d’une réduction d’impôt.',
  ],
  cta: 'JE FAIS UN DON',
};

const FROM_DEADLINE = {
  paragraphs: [
    'Faites un don à Amnesty International France!',
    'Vous dénoncez les violations des droits humains et agissez concrètement pour protéger celles et ceux qui en sont victimes.',
  ],
  cta: 'Je fais un don',
};

const COUNTDOWN_UNITS = [
  { key: 'days', singular: 'jour', plural: 'jours' },
  { key: 'hours', singular: 'heure', plural: 'heures' },
  { key: 'minutes', singular: 'minute', plural: 'minutes' },
];

const CROSS_ICON = `<svg aria-hidden="true" focusable="false" width="27" height="28" viewBox="0 0 27 28" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1 27L26 1M1 1L26 27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>`;

export const getRemaining = (now) => {
  const left = DEADLINE - now;
  if (left <= 0) return null;

  return {
    days: Math.floor(left / DAY),
    hours: Math.floor((left % DAY) / HOUR),
    minutes: Math.floor((left % HOUR) / MINUTE),
  };
};

const createElement = (tag, className, text) => {
  const element = document.createElement(tag);
  if (className) element.className = className;
  if (text !== undefined) element.textContent = text;
  return element;
};

const createCountdown = () => {
  const countdown = createElement('p', 'exit-popin-countdown');
  COUNTDOWN_UNITS.forEach(() => {
    const item = createElement('span', 'exit-popin-countdown-item');
    item.append(createElement('strong'), document.createTextNode(''));
    countdown.append(item);
  });
  return countdown;
};

// Updates the numbers in place, so a screen reader keeps its position.
const fillCountdown = (countdown, remaining) => {
  COUNTDOWN_UNITS.forEach(({ key, singular, plural }, index) => {
    const item = countdown.children[index];
    item.firstChild.textContent = String(remaining[key]);
    item.lastChild.textContent = ` ${remaining[key] === 1 ? singular : plural}`;
  });
};

// The text and CTA change only when the period changes, the countdown every call.
export const updatePopin = (dialog, now) => {
  const remaining = getRemaining(now);
  const period = remaining ? 'before' : 'after';
  const text = dialog.querySelector('.exit-popin-text');

  if (dialog.getAttribute('data-period') !== period) {
    const content = remaining ? BEFORE_DEADLINE : FROM_DEADLINE;
    dialog.setAttribute('data-period', period);
    text.replaceChildren(
      ...content.paragraphs.map((paragraph) => createElement('p', '', paragraph)),
    );
    const ctaLabel = dialog.querySelector('.exit-popin-cta-label');
    ctaLabel.textContent = content.cta;
  }

  let countdown = dialog.querySelector('.exit-popin-countdown');
  if (!remaining) {
    countdown?.remove();
    return;
  }
  if (!countdown) {
    countdown = createCountdown();
    text.after(countdown);
  }
  fillCountdown(countdown, remaining);
};

export const buildPopin = (now) => {
  const dialog = createElement('dialog', 'exit-popin');
  dialog.setAttribute('aria-labelledby', TITLE_ID);
  dialog.setAttribute('aria-describedby', TEXT_ID);
  dialog.innerHTML = `
    <div class="exit-popin-inner">
      <button type="button" class="exit-popin-close" aria-label="Fermer">${CROSS_ICON}</button>
      <h2 class="exit-popin-title" id="${TITLE_ID}"></h2>
      <div class="exit-popin-text" id="${TEXT_ID}"></div>
      <a class="exit-popin-cta" target="_blank" rel="noopener"><span class="exit-popin-cta-label"></span><span class="screen-reader-text"> (nouvel onglet)</span></a>
    </div>`;
  dialog.querySelector('.exit-popin-title').textContent = TITLE;
  dialog.querySelector('.exit-popin-cta').href = CTA_URL;
  updatePopin(dialog, now);
  return dialog;
};
