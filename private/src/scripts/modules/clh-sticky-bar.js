const StickyBar = () => {
  const bar = document.querySelector('[data-clh-sticky-bar]');
  const page = document.querySelector('.page-change-their-history');
  const banner = page?.querySelector('.clh-band');
  // Sans témoignage, le don (ou le partage) devient le point d'arrêt.
  const stop =
    page?.querySelector('.clh-timeline') ??
    page?.querySelector('.clh-donation') ??
    page?.querySelector('.clh-share');
  if (!bar || !banner || !stop) return;

  const header = document.querySelector('.page-header');
  const adminBar = document.querySelector('#wpadminbar');
  bar.hidden = false;
  stop.classList.add('has-clh-sticky-stop');

  const update = () => {
    // L'en-tête revient lorsqu'on remonte : le bandeau se place alors en dessous.
    const offset = Math.max(
      0,
      header?.getBoundingClientRect().bottom ?? 0,
      adminBar?.getBoundingClientRect().bottom ?? 0,
    );
    const height = bar.getBoundingClientRect().height;
    stop.style.setProperty('--clh-sticky-height', `${height}px`);

    // À l'arrivée de la frise en haut, le bandeau suit son bord supérieur et sort
    // de l'écran avec elle. Il ne réapparaît donc ni au don, ni au pied de page.
    const top = Math.min(offset, stop.getBoundingClientRect().top);
    const shown = banner.getBoundingClientRect().bottom <= offset && top + height > offset;
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
  [bar, page, header, adminBar].filter(Boolean).forEach((element) => resize.observe(element));
  if (header) new MutationObserver(schedule).observe(header, { attributes: true });
  update();
};

export default StickyBar;
