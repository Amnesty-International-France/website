const enhanceJetpackFormPlaceholders = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const requiredInputs = document.querySelectorAll('input[required], textarea[required]');

    requiredInputs.forEach((input) => {
      let newPlaceholder = input.placeholder;

      if (newPlaceholder) {
        if (!newPlaceholder.endsWith('*')) {
          newPlaceholder += ' *';
        }
      }

      // eslint-disable-next-line no-param-reassign
      input.placeholder = newPlaceholder;
    });
  });
};

export default enhanceJetpackFormPlaceholders;
