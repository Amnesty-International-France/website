const mySpaceMenu = () => {
  const sidebar = document.querySelector('.aif-donor-space-sidebar');
  if (!sidebar) return;

  const header = sidebar.querySelector('.aif-donor-space-sidebar-header');
  const titleElement = sidebar.querySelector('.aif-donor-space-sidebar-title');
  const menuContainer = sidebar.querySelector('.aif-donor-space-sidebar-menu');
  if (!header || !titleElement || !menuContainer) return;

  const navigationHistory = [];
  let initialTitle = '';

  const goForward = (parentLiElement) => {
    const subMenu = parentLiElement.querySelector(':scope > .sub-menu');
    if (!subMenu) return;

    const parentUl = parentLiElement.parentElement;
    const link = parentLiElement.querySelector(':scope > a');
    if (!link) return;
    const parentTitle = link.textContent;

    navigationHistory.push({
      parentUl,
      activeLi: parentLiElement,
    });

    parentUl.classList.add('has-active-child');
    parentLiElement.classList.add('is-active-child');
    subMenu.style.display = 'flex';

    titleElement.textContent = parentTitle;
    header.classList.add('is-back-button');
  };

  const goBack = () => {
    const previousState = navigationHistory.pop();
    if (previousState) {
      const { parentUl, activeLi } = previousState;
      const subMenuToHide = activeLi.querySelector(':scope > .sub-menu');

      if (subMenuToHide) {
        subMenuToHide.style.display = 'none';
      }

      activeLi.classList.remove('is-active-child');
      parentUl.classList.remove('has-active-child');

      const newTitle =
        navigationHistory.length > 0
          ? navigationHistory[navigationHistory.length - 1].activeLi.querySelector(':scope > a')
              .textContent
          : initialTitle;
      titleElement.textContent = newTitle;

      if (navigationHistory.length === 0) {
        header.classList.remove('is-back-button');
      }
    }
  };

  const setupAllParentLinks = () => {
    const allParentItems = sidebar.querySelectorAll('.menu-item-has-children');
    allParentItems.forEach((item) => {
      const link = item.querySelector(':scope > a');
      const subMenu = item.querySelector(':scope > .sub-menu');
      if (link && subMenu) {
        link.removeAttribute('href');
        link.setAttribute('role', 'button');
        link.onclick = (event) => {
          event.preventDefault();
          goForward(item);
        };
      }
    });
  };

  const initMenu = () => {
    initialTitle = titleElement.textContent;

    setupAllParentLinks();

    const activeAncestors = sidebar.querySelectorAll('.current-menu-ancestor');

    if (activeAncestors.length > 0) {
      activeAncestors.forEach((ancestorLi) => {
        goForward(ancestorLi);
      });
    }

    titleElement.addEventListener('click', goBack);
  };

  initMenu();
};

export default mySpaceMenu;
