const initJetpackForm = () => {
  const form = document.querySelector('.contact-form');

  if (!form) {
    return;
  }

  const urlParams = new URLSearchParams(window.location.search);
  const codeOrigineValue = urlParams.get('reserved_originecode') ?? urlParams.get('code_origine');

  form.addEventListener('submit', (event) => {
    const hiddenInput = form.querySelector('input[name="code_origine"]');
    if (codeOrigineValue) {
      hiddenInput.value = codeOrigineValue;
    }
    const gtmType = event.currentTarget.getAttribute('data-gtm-type');
    const gtmName = event.currentTarget.getAttribute('data-gtm-name');
    window.dataLayer.push({
      event: 'form_submit',
      type: gtmType,
      name: gtmName,
    });
  });

};
export default initJetpackForm;
