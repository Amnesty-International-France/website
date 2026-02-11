document.addEventListener('DOMContentLoaded', () => {
  const nonceFields = document.querySelectorAll('.dynamic-nonce');

  nonceFields.forEach((field) => {
    const action = field.getAttribute('data-action');

    fetch(`/wp-json/humanity-theme/v1/get-nonce?action=${action}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        field.value = data.nonce; // eslint-disable-line no-param-reassign
      })
      .catch((error) => console.error('Erreur de récupération du nonce:', error));
  });
});
