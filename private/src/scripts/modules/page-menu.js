export const pageMenu = () => {
  const content = document.querySelector('[data-page-group="main"]');
  const menuContainer = document.getElementById('page-menu');
  const currentPageType = document.querySelector('[data-page-type]')?.dataset?.pageType;

  if (!content || !menuContainer || !currentPageType) return;

  if (currentPageType === 'foundation') menuContainer.classList.add('green');

  const headings = content.querySelectorAll('h2');
  if (!headings.length) {
    menuContainer.style.display = 'none';
    return;
  }

  const list = document.createElement('ul');

  headings.forEach((heading, index) => {
    const h = heading;
    if (!h.id) h.id = `legs-menu-${index + 1}`;
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.textContent = h.textContent;
    a.href = `#${h.id}`;
    li.appendChild(a);
    list.appendChild(li);
  });

  menuContainer.appendChild(list);
};

export const stickyMenu = () => {
  const heroBlock = document.querySelector('.page-hero-block');
  const menuSticky = document.querySelector('#page-menu');
  const mainMenu = document.querySelector("header[role='banner']");
  if (!heroBlock || !menuSticky || !mainMenu) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          menuSticky.classList.add('fixed');
        } else {
          menuSticky.classList.remove('fixed', 'under-main-menu');
        }
      });
    },
    { threshold: 0 },
  );

  observer.observe(heroBlock);

  let lastScroll = 0;
  let lastMainMenuClientRectTop = 0;
  let scrollTimeout;

  window.addEventListener('scroll', () => {
    if (!mainMenu || !menuSticky) return;

    const currentScrollTop = mainMenu.getBoundingClientRect().top;

    if (window.scrollY > lastScroll) {
      menuSticky.classList.remove('under-main-menu');
    }

    clearTimeout(scrollTimeout);

    scrollTimeout = setTimeout(() => {
      if (currentScrollTop <= 0 && currentScrollTop > lastMainMenuClientRectTop) {
        menuSticky.classList.add('under-main-menu');
      }

      lastMainMenuClientRectTop = currentScrollTop;
    }, 30);

    lastScroll = window.scrollY;
  });
};

export default { pageMenu, stickyMenu };
