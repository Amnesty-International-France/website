const SELECTORS = {
  banner: '.urgent-banner',
  close: '.icon-cross',
  cta: '.cta',
};

const getElements = () => {
  const body = document.body;
  const banner = body?.querySelector(SELECTORS.banner);
  return {
    body,
    banner,
    close: banner?.querySelector(SELECTORS.close),
    cta: banner?.querySelector(SELECTORS.cta),
  };
};

const closeModal = () => {
  const { body, banner } = getElements();
  if (!body || !banner) return;

  sessionStorage.setItem('userIsDoneWithBandeau', 'true');
  banner.classList.add('hidden');
  body.classList.remove('no-scroll');
};

export const closeUrgentBanner = () => {
  const { body, banner, close, cta } = getElements();
  if (!body || !banner) return;

  const userIsDone = sessionStorage.getItem('userIsDoneWithBandeau') === 'true';

  banner.classList.toggle('hidden', userIsDone);
  body.classList.toggle('no-scroll', !userIsDone);

  [close, cta].forEach((el) => el?.addEventListener('click', closeModal));
};

export default {
  closeUrgentBanner,
};
