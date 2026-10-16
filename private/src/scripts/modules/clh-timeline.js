const initTimeline = (dial) => {
  const years = [...dial.querySelectorAll('.clh-timeline-year')];
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

  const center = (behavior) => {
    const mark = dial.querySelector('.clh-timeline-input:checked')?.closest('.clh-timeline-mark');
    if (!mark) return;

    dial.scrollTo({
      left: mark.offsetLeft + mark.offsetWidth / 2 - dial.clientWidth / 2,
      behavior,
    });
  };

  const selectPerson = (panel, rank) => {
    panel.querySelectorAll('.clh-timeline-slide').forEach((slide, index) => {
      slide.classList.toggle('is-active', index === rank);
    });
  };

  dial.addEventListener('change', ({ target }) => {
    if (target.matches('.clh-timeline-input')) {
      const year = target.closest('.clh-timeline-year');
      dial.style.setProperty('--clh-timeline-cursor', years.indexOf(year));
      const panel = year.querySelector('.clh-timeline-panel');
      const first = panel.querySelector('.clh-timeline-pick-input');
      if (first) first.checked = true;
      selectPerson(panel, 0);
      center(reduced.matches ? 'instant' : 'smooth');
    } else if (target.matches('.clh-timeline-pick-input')) {
      selectPerson(target.closest('.clh-timeline-panel'), Number(target.value));
    }
  });

  center('instant');
  window.addEventListener('resize', () => center('instant'));

  dial.addEventListener('clh:reveal', () => {
    dial.classList.add('is-entering');
    requestAnimationFrame(() => requestAnimationFrame(() => dial.classList.remove('is-entering')));
  });
};

const Timeline = () => {
  document.querySelectorAll('.clh-timeline-dial').forEach(initTimeline);
};

export default Timeline;
