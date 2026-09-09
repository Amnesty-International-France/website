const copyShare = () => {
  const init = () => {
    const copyBtns = document.querySelectorAll('.article-shareCopy');

    copyBtns.forEach((copyBtn) => {
      const copy = () => {
        const { url } = copyBtn.dataset;

        if (!url || !navigator.clipboard) {
          return;
        }

        navigator.clipboard
          .writeText(url)
          .then(() => {
            copyBtn.classList.add('copied');
            setTimeout(() => copyBtn.classList.remove('copied'), 2000);
          })
          .catch(() => undefined);
      };

      copyBtn.addEventListener('click', copy);
      copyBtn.addEventListener('keydown', (event) => {
        if (![' ', 'Enter'].includes(event.key)) {
          return;
        }

        event.preventDefault();
        copy();
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
    return;
  }

  init();
};

export default copyShare;
