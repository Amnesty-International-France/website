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
  const menuSticky = document.querySelector('#page-menu');
  const mainMenu = document.querySelector("header[role='banner']");

  if (!menuSticky || !mainMenu) return;

  const sentinel = document.createElement('div');
  menuSticky.parentNode.insertBefore(sentinel, menuSticky);

  const placeholder = document.createElement('div');
  placeholder.style.display = 'none';
  menuSticky.parentNode.insertBefore(placeholder, menuSticky.nextSibling);

  const mainMenuHeight = mainMenu.offsetHeight;

  const observer = new IntersectionObserver(
    (entries) => {
      const [entry] = entries;

      const menuHeight = menuSticky.offsetHeight;

      if (entry.boundingClientRect.y < entry.rootBounds.y) {
        menuSticky.classList.add('fixed', 'under-main-menu');

        placeholder.style.height = `${menuHeight}px`;
        placeholder.style.display = 'block';
      } else {
        menuSticky.classList.remove('fixed', 'under-main-menu');
        placeholder.style.display = 'none';
      }
    },
    {
      rootMargin: `-${mainMenuHeight}px 0px 0px 0px`,
      threshold: 1.0,
    },
  );

  observer.observe(sentinel);
};

export default { pageMenu, stickyMenu };
