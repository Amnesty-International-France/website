const StickyBar = () => {
  const bar = document.querySelector('[data-clh-sticky-bar]');
  const page = document.querySelector('.page-change-their-history');
  const banner = page?.querySelector('.clh-band[data-clh-reveal]');
  const reminder = page?.querySelector('[data-clh-reminder]');
  if (!bar || !banner || !reminder) return;

  const header = document.querySelector('.page-header');
  const adminBar = document.querySelector('#wpadminbar');
  bar.hidden = false;

  const update = () => {
    // L'en-tête revient lorsqu'on remonte : le bandeau se place alors en dessous.
    const offset = Math.max(
      0,
      header?.getBoundingClientRect().bottom ?? 0,
      adminBar?.getBoundingClientRect().bottom ?? 0,
    );
    const height = bar.getBoundingClientRect().height;

    // Le rappel reste dans le flux. Dès qu'il touche le bas du sticky, celui-ci
    // s'efface : les deux bandeaux ne se recouvrent jamais. En remontant, il reprend
    // sa place quand le rappel s'éloigne.
    const beforeStop = reminder.getBoundingClientRect().top > offset + height;
    const shown = banner.getBoundingClientRect().bottom <= offset && beforeStop;
    bar.style.top = `${offset}px`;
    bar.classList.toggle('is-in', shown);
    bar.inert = !shown;
  };

  let scheduled = false;
  const schedule = () => {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => {
      scheduled = false;
      update();
    });
  };

  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule);
  const resize = new ResizeObserver(schedule);
  [bar, page, reminder, header, adminBar]
    .filter(Boolean)
    .forEach((element) => resize.observe(element));
  if (header) new MutationObserver(schedule).observe(header, { attributes: true });
  update();
};

export default StickyBar;
