const initJetpackForm = () => {
  const form = document.querySelector('.contact-form');

  if (!form) {
    return;
  }

  const urlParams = new URLSearchParams(window.location.search);
  const codeOrigineValue = urlParams.get('reserved_originecode') ?? urlParams.get('code_origine');

  form.addEventListener('submit', () => {
    const hiddenInput = form.querySelector('input[name="code_origine"]');
    if (codeOrigineValue) {
      hiddenInput.value = codeOrigineValue;
    }
  });
};
export default initJetpackForm;
