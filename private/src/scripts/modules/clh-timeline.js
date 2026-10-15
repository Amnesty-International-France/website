const Timeline = () => {
  const dial = document.querySelector('.clh-timeline-dial');
  if (!dial) return;

  // Sur un écran étroit les années défilent. Le plateau s'ouvre sur l'année cochée, la
  // dernière victoire, et recentre celle qu'on choisit : sans ça le panneau de 2024 s'afficherait
  // sous les années 2013 à 2016.
  const center = (behavior) => {
    const mark = dial.querySelector('.clh-timeline-input:checked')?.closest('.clh-timeline-mark');
    if (!mark) return;

    dial.scrollTo({
      left: mark.offsetLeft + mark.offsetWidth / 2 - dial.clientWidth / 2,
      behavior,
    });
  };

  center('instant');

  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  dial.addEventListener('change', () => center(reduced.matches ? 'instant' : 'smooth'));

  // L'entrée ne vaut que pour les transitions lancées à l'arrivée : deux images plus tard, la
  // classe est retirée et un clic retrouve ses délais ordinaires.
  dial.addEventListener('clh:reveal', () => {
    dial.classList.add('is-entering');
    requestAnimationFrame(() => requestAnimationFrame(() => dial.classList.remove('is-entering')));
  });
};

export default Timeline;
