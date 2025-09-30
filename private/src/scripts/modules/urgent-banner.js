import Cookies from 'js-cookie';

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

  Cookies.set('user_is_done_with_bandeau', true, { expires: 7 });
  banner.classList.add('hidden');
  body.classList.remove('no-scroll');
};

export const closeUrgentBanner = () => {
  const { body, banner, close, cta } = getElements();
  if (!body || !banner) return;

  const userIsDone = Cookies.get('user_is_done_with_bandeau');
  banner.classList.toggle('hidden', userIsDone);
  body.classList.toggle('no-scroll', !userIsDone);

  [close, cta].forEach((el) => el?.addEventListener('click', closeModal));
};

export default {
  closeUrgentBanner,
};
