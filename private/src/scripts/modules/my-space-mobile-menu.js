const mySpaceMobileMenu = () => {
  const burgerButton = document.querySelector('#burger-toggle');
  const closeButton = document.querySelector('#close-menu');
  const sidebar = document.querySelector('#my-space-sidebar');
  const overlay = document.querySelector('#mobile-menu-overlay');
  const body = document.body;

  if (!burgerButton || !closeButton || !sidebar || !overlay) {
    return;
  }

  const openMenu = () => {
    sidebar.classList.add('is-open');
    overlay.classList.add('is-open');
    body.classList.add('menu-open');
  };

  const closeMenu = () => {
    sidebar.classList.remove('is-open');
    overlay.classList.remove('is-open');
    body.classList.remove('menu-open');
  };

  burgerButton.addEventListener('click', openMenu);
  closeButton.addEventListener('click', closeMenu);
  overlay.addEventListener('click', closeMenu);
};

export default mySpaceMobileMenu;
