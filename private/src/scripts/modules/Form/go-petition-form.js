const GoPetitionsForm = () => {
  const mobileButton = document.getElementById('go-petitions');

  if (!mobileButton) {
    return;
  }

  const form = document.getElementById('petition');

  if (!form) {
    return;
  }

  mobileButton.addEventListener('click', (event) => {
    event.preventDefault();

    form.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
  });
};

export default GoPetitionsForm;
