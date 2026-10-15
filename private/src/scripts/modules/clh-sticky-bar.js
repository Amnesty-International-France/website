const StickyBar = () => {
  const bar = document.querySelector('[data-clh-sticky-bar]');
  const page = document.querySelector('.page-change-their-history');
  const banner = page?.querySelector('.clh-band');
  const reminder = page?.querySelector('[data-clh-reminder]');
  // Les anciens gabarits sans rappel gardent leur arrêt à la frise, au don ou au partage.
  const stop =
    reminder ??
    page?.querySelector('.clh-timeline') ??
    page?.querySelector('.clh-donation') ??
    page?.querySelector('.clh-share');
  if (!bar || !banner || !stop) return;

  const header = document.querySelector('.page-header');
  const adminBar = document.querySelector('#wpadminbar');
  bar.hidden = false;
  if (!reminder) stop.classList.add('has-clh-sticky-stop');

  const update = () => {
    // L'en-tête revient lorsqu'on remonte : le bandeau se place alors en dessous.
    const offset = Math.max(
      0,
      header?.getBoundingClientRect().bottom ?? 0,
      adminBar?.getBoundingClientRect().bottom ?? 0,
    );
    const height = bar.getBoundingClientRect().height;
    if (!reminder) stop.style.setProperty('--clh-sticky-height', `${height}px`);

    // Le rappel reste dans le flux. Dès qu'il touche le bas du sticky, celui-ci
    // s'efface : les deux bandeaux ne se recouvrent jamais. En remontant, il reprend
    // sa place quand le rappel s'éloigne. Sans rappel, conserver l'ancien arrêt.
    const stopTop = stop.getBoundingClientRect().top;
    const top = reminder ? offset : Math.min(offset, stopTop);
    const beforeStop = reminder ? stopTop > offset + height : top + height > offset;
    const shown = banner.getBoundingClientRect().bottom <= offset && beforeStop;
    bar.style.top = `${top}px`;
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
  [bar, page, stop, header, adminBar].filter(Boolean).forEach((element) => resize.observe(element));
  if (header) new MutationObserver(schedule).observe(header, { attributes: true });
  update();
};

export default StickyBar;
