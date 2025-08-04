const pageMenu = () => {
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

export default pageMenu;
