const SELECTORS = {
  banner: '.alert-banner',
  close: '.icon-cross',
  cta: '.cta',
  session_base_name: 'user_is_done_with_alert_banner',
};

const getElements = () => {
  const banner = document.querySelector(SELECTORS.banner);
  const id = banner?.id.split('-')[2];

  return {
    id,
    banner,
    close: banner?.querySelector(SELECTORS.close),
    cta: banner?.querySelector(SELECTORS.cta),
  };
};

const handleClose = () => {
  const { banner, id } = getElements();
  if (!banner) return;

  sessionStorage.setItem(`${SELECTORS.session_base_name}_${id}`, 'true');
  banner.classList.add('hidden');
};

export const closeAlertBanner = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const { banner, close, cta, id } = getElements();

    if (!banner) return;

    const userIdDone = sessionStorage.getItem(`${SELECTORS.session_base_name}_${id}`);

    if (userIdDone === 'true') {
      banner.classList.add('hidden');
    }

    [close, cta].forEach((el) => el?.addEventListener('click', handleClose));
  });
};

export default { closeAlertBanner };
