const copyShare = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const copyBtn = document.querySelector('.article-shareCopy');

    if (copyBtn) {
      copyBtn.addEventListener('click', () => {
        const { url } = copyBtn.dataset;

        navigator.clipboard
          .writeText(url)
          .then(() => {
            copyBtn.classList.add('copied');
            setTimeout(() => copyBtn.classList.remove('copied'), 2000);
          })
          .catch((err) => {
            console.error('Erreur lors de la copie :', err);
          });
      });
    }
  });
};

export default copyShare;
