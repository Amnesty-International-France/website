export const closeUrgentBanner = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const urgentBanner = document.querySelector('.urgent-banner');
    const closeCross = urgentBanner.querySelector('.icon-cross');
    const userIsDoneWithBandeau = sessionStorage.getItem('userIsDoneWithBandeau');

    if (userIsDoneWithBandeau) {
      urgentBanner.style.display = 'none';
    }

    closeCross.addEventListener('click', () => {
      sessionStorage.setItem('userIsDoneWithBandeau', 'true');
      urgentBanner.style.display = 'none';
    });
  });
};

export default {
  closeUrgentBanner,
};
