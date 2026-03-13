import Cookies from 'js-cookie';

const SELECTORS = {
  banner: '.urgent-banner',
  close: '.icon-cross',
  cta: '.cta',
  cookie_base_name: 'user_is_done_with_bandeau',
};

const getElements = () => {
  const body = document.body;
  const banner = body?.querySelector(SELECTORS.banner);
  const id = banner?.id.split('-')[2];

  return {
    body,
    banner,
    close: banner?.querySelector(SELECTORS.close),
    cta: banner?.querySelector(SELECTORS.cta),
    id,
  };
};

const closeModal = () => {
  const { body, banner, id } = getElements();
  if (!body || !banner) return;

  Cookies.set(`${SELECTORS.cookie_base_name}_${id}`, true, { expires: 7 });
  banner.classList.add('hidden');
  body.classList.remove('no-scroll');
};

export const closeUrgentBanner = () => {
  const { body, banner, close, cta, id } = getElements();
  if (!body || !banner) return;

  const userIsDone = Cookies.get(`${SELECTORS.cookie_base_name}_${id}`);
  banner.classList.toggle('hidden', userIsDone);
  body.classList.toggle('no-scroll', !userIsDone);

  [close, cta].forEach((el) => el?.addEventListener('click', closeModal));
};

export default {
  closeUrgentBanner,
};
