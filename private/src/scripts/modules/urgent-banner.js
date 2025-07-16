export const closeUrgentBanner = () => {
  const urgentBanner = document.querySelector('.urgent-banner');

  if (!urgentBanner) return;

  const closeCross = urgentBanner.querySelector('.icon-cross');

  if (!closeCross) return;

  const userIsDoneWithBandeau = sessionStorage.getItem('userIsDoneWithBandeau') === 'true';

  urgentBanner.classList.toggle('hidden', userIsDoneWithBandeau);

  closeCross.addEventListener('click', () => {
    sessionStorage.setItem('userIsDoneWithBandeau', 'true');
    urgentBanner.classList.add('hidden');
  });
};

export default {
  closeUrgentBanner,
};
