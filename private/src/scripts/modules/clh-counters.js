// La même courbe des deux côtés : elle part vite et met la moitié de son temps sur les
// derniers pour cents. Le compteur peine à atteindre l'objectif, c'est ce que la page dit.
const EASING = 'cubic-bezier(0.22, 1, 0.36, 1)';
const DURATION = 1800;

// La roue du compte à rebours avance d'un cran par jour, et les crans s'espacent : 90 ms
// au premier, 700 ms au dernier. Elle tourne vite, puis elle peine à se poser.
const STEP_MIN = 90;
const STEP_MAX = 700;

// Préfixe, corps du nombre avec ses séparateurs, suffixe : « 12 345 », « 67 % ». Un libellé
// sans chiffre, « Objectif atteint », ne correspond pas et reste tel quel.
const NUMBER = /^(\D*)(\d[\s\S]*\d|\d)(\D*)$/;

const ease = (ratio) => 1 - (1 - ratio) ** 5;

const group = (value, separator) =>
  separator ? String(value).replace(/\B(?=(\d{3})+(?!\d))/g, separator) : String(value);

const write = (element, text) => {
  // eslint-disable-next-line no-param-reassign
  element.textContent = text;
};

const cell = (digit) => {
  const node = document.createElement('span');

  node.className = 'clh-roll-digit';
  node.textContent = digit;

  return node;
};

// Les chiffres montés sur la bande sont exactement ceux que la colonne va montrer, dans
// l'ordre croissant. La roue part du haut de la suite et redescend jusqu'à aujourd'hui :
// elle ne repasse jamais par un chiffre qu'elle n'affiche pas, et une colonne qui ne
// change pas ne bouge pas.
const column = (frames, rank) => {
  const digits = [];
  const steps = frames.map((frame) => {
    if (digits[digits.length - 1] !== frame[rank]) digits.push(frame[rank]);

    return digits.length - 1;
  });

  const box = document.createElement('span');
  const band = document.createElement('span');

  box.className = 'clh-roll';
  band.className = 'clh-roll-strip';
  digits.forEach((digit) => band.appendChild(cell(digit)));
  box.appendChild(band);

  return { box, band, steps, size: digits.length };
};

const wheel = (element, from, target) => {
  const width = String(from).length;

  if (from < target || String(target).length !== width) return false;

  const frames = [];

  for (let value = target; value <= from; value += 1) frames.push(String(value));

  const columns = [];

  for (let rank = 0; rank < width; rank += 1) columns.push(column(frames, rank));

  // Le décalage est exprimé en pourcentage de la bande : la hauteur d'un cran reste celle
  // que la feuille de style donne au chiffre, sans la redire ici.
  const apply = (index) => {
    columns.forEach(({ band, steps, size }) => {
      // eslint-disable-next-line no-param-reassign
      band.style.transform = `translateY(${(-100 * steps[index]) / size}%)`;
    });
  };

  const total = frames.length - 1;
  let cursor = total;

  // Bely n'a pas de chiffres de largeur fixe : une colonne prend la largeur de son plus
  // large chiffre, et le bloc s'élargirait. Or le titre à côté est dimensionné sur sa
  // colonne de grille, en `cqi` : il se recomposerait à l'ouverture de la page. La largeur
  // est donc figée sur celle du texte rendu, et la roue déborde à sa droite, où il n'y a
  // que la gouttière.
  // eslint-disable-next-line no-param-reassign
  element.style.width = `${element.getBoundingClientRect().width}px`;
  element.classList.add('is-rolling');
  element.replaceChildren(...columns.map(({ box }) => box));
  apply(cursor);

  const next = () => {
    cursor -= 1;
    apply(cursor);

    if (cursor > 0) {
      setTimeout(next, STEP_MIN + (STEP_MAX - STEP_MIN) * ((total - cursor) / total) ** 2);
    }
  };

  setTimeout(next, STEP_MIN);

  return true;
};

// Même piège de largeur que la roue : les chiffres de Bely n'ont pas la même chasse, et
// un nombre qui défile ferait recomposer le titre voisin à chaque cadre. La largeur est
// figée sur celle du nombre d'arrivée le temps du défilement, puis rendue au flux. Un nombre
// intermédiaire plus large que celui d'arrivée déborde : calé sur le bord de son alignement,
// il déborde du côté opposé au lieu de glisser.
const EDGES = { right: 'flex-end', end: 'flex-end', center: 'center' };

const glide = (element, from, target, duration, render) => {
  const started = performance.now();
  const style = getComputedStyle(element);
  const edge = EDGES[style.textAlign] ?? 'flex-start';

  Object.assign(element.style, {
    width: `${element.getBoundingClientRect().width}px`,
    display: style.display === 'inline' ? 'inline-flex' : 'flex',
    justifyContent: edge,
    whiteSpace: 'nowrap',
  });

  const frame = (now) => {
    const ratio = Math.min(1, (now - started) / duration);

    write(element, render(Math.round(from + (target - from) * ease(ratio))));

    if (ratio < 1) {
      requestAnimationFrame(frame);
    } else {
      Object.assign(element.style, { width: '', display: '', justifyContent: '', whiteSpace: '' });
    }
  };

  requestAnimationFrame(frame);
};

// Le nombre d'arrivée est celui que PHP a rendu : on le lit dans le DOM plutôt que de
// reformater côté JS, sinon le séparateur de milliers change au dernier cadre.
const count = (element) => {
  const parts = element.textContent.trim().match(NUMBER);

  if (!parts) return;

  const [, prefix, body, suffix] = parts;
  // Seuls les entiers, éventuellement groupés par espaces, sont animés. Les nombres
  // décimaux ou ambigus gardent leur texte d'origine (par exemple « 1,5 M »).
  if (!/^(?:\d+|\d{1,3}(?:[ \u00a0\u202f]\d{3})+)$/.test(body)) return;

  const target = parseInt(body.replace(/\D/g, ''), 10);
  const separator = (body.match(/\D/) || [''])[0];
  const from = parseInt(element.getAttribute('data-clh-count-from'), 10) || 0;
  const duration = parseInt(element.getAttribute('data-clh-count'), 10) || DURATION;

  if (Number.isNaN(target) || from === target) return;

  if (element.hasAttribute('data-clh-count-step') && wheel(element, from, target)) return;

  glide(element, from, target, duration, (value) => prefix + group(value, separator) + suffix);
};

const reveal = (element) => {
  element.classList.remove('is-waiting');
  element.dispatchEvent(new CustomEvent('clh:reveal'));

  element.querySelectorAll('.progress-bar').forEach((bar) => {
    if (typeof bar.animate !== 'function') return;

    bar.animate({ transform: ['scaleX(0)', 'none'] }, { duration: DURATION, easing: EASING });
  });

  // L'anneau des jours part plein et se vide jusqu'aux jours qui restent, pendant que la roue
  // descend.
  element.querySelectorAll('.clh-countdown-arc').forEach((arc) => {
    if (typeof arc.animate !== 'function') return;

    arc.animate(
      { strokeDasharray: ['1 1', getComputedStyle(arc).strokeDasharray] },
      { duration: DURATION, easing: EASING },
    );
  });

  element.querySelectorAll('[data-clh-count]').forEach(count);
};

const Counters = () => {
  const groups = document.querySelectorAll('[data-clh-reveal]');

  // Les valeurs rendues par PHP sont déjà les bonnes : sans animation, il n'y a rien à
  // faire, et rien n'est caché en attendant.
  if (!groups.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const watch = (options) => {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        observer.unobserve(entry.target);
        reveal(entry.target);
      });
    }, options);

    return observer;
  };

  // Un bloc se lance quand son haut passe le dernier quart de l'écran, pas dès qu'il en
  // franchit le bord. Un bloc collé au bas de la page n'y monte jamais : `full` le lance
  // quand il est entièrement visible.
  const early = watch({ threshold: 0, rootMargin: '0px 0px -25% 0px' });
  const full = watch({ threshold: 1 });
  // Un bloc qui arrive sous son titre déjà lu, comme le plateau de la frise, part dès son entrée.
  const soon = watch({ threshold: 0, rootMargin: '0px 0px -10% 0px' });
  const observers = { full, soon };

  // `is-waiting` ne se pose qu'ici : sans script, ou sans mouvement, tout reste affiché.
  groups.forEach((element) => {
    element.classList.add('is-waiting');
    (observers[element.getAttribute('data-clh-reveal')] ?? early).observe(element);
  });
};

export default Counters;
