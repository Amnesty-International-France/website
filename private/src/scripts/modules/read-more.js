const readMoreBlock = () => {
  document.querySelectorAll('.read-more-block').forEach((block) => {
    const content = block.querySelector('.read-more-content');
    const button = block.querySelector('.read-more-toggle');
    const span = button.querySelector('span');
    const icon = button.querySelector('svg');

    if (button && content && span) {
      button.addEventListener('click', () => {
        const isExpanded = content.classList.toggle('expanded');
        content.classList.toggle('collapsed');

        span.textContent = isExpanded ? 'Lire moins' : 'Lire la suite';

        if (icon) {
          icon.classList.toggle('rotated', isExpanded);
        }
      });
    }
  });
};

export default readMoreBlock;
