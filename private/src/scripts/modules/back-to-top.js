const backToTop = () => {
  const btn = document.querySelector('.back-to-top');

  if (!btn || !window) return;

  window.addEventListener('scroll', () => {
    btn.classList.toggle('hidden', window.scrollY < 2000);
  });

  btn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });
};

export default backToTop;
