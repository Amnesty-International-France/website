const readMoreBlock = () => {
  document.querySelectorAll('.read-more-block').forEach((block) => {
    const content = block.querySelector('.read-more-content');
    const button = block.querySelector('.read-more-toggle');
    const span = button.querySelector('span');
    const icon = button.querySelector('svg');

    if (button && content && span) {
      const initialReadMoreLabel = button.dataset.readMoreLabel || span.textContent;
      let readLessLabel = 'Lire moins';

      if (initialReadMoreLabel === 'Afficher la lettre de pétition') {
        readLessLabel = 'Fermer la lettre de pétition';
      } else if (initialReadMoreLabel === 'Lire la suite') {
        readLessLabel = 'Lire moins';
      }

      span.textContent = initialReadMoreLabel;

      button.addEventListener('click', () => {
        const isExpanded = content.classList.toggle('expanded');
        content.classList.toggle('collapsed');

        span.textContent = isExpanded ? readLessLabel : initialReadMoreLabel;

        if (icon) {
          icon.classList.toggle('rotated', isExpanded);
        }
      });
    }
  });
};

export default readMoreBlock;
