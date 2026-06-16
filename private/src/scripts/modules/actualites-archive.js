const actualitesArchive = () => {
  const root = document.querySelector('.actualites-archive');

  if (!root) return;

  const tabs = root.querySelectorAll('.az-letter');
  const blocks = root.querySelectorAll('.actualites-year-block');
  const display = root.querySelector('.az-letter-display');

  if (!tabs.length || !blocks.length) return;

  const showYear = (year) => {
    if (display) display.textContent = year;

    blocks.forEach((el) => {
      const element = el;
      element.style.display = el.dataset.year === year ? 'block' : 'none';
    });

    tabs.forEach((tab) => {
      const isActive = tab.dataset.year === year;
      tab.classList.toggle('active', isActive);
      tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => showYear(tab.dataset.year));
  });

  const initial = root.querySelector('.az-letter.active') || tabs[0];

  showYear(initial.dataset.year);
};

export default actualitesArchive;
