/**
 * Formulaires à envoi unique (attribut data-aif-single-submit).
 *
 * Au premier envoi valide, le bouton affiche un loader et les clics suivants
 * sont ignorés jusqu'au chargement de la page de réponse. Le serveur refuse
 * de toute façon un second envoi du même formulaire (jeton à usage unique).
 */
(function () {
  const forms = document.querySelectorAll('form[data-aif-single-submit]');

  const getButton = (form, submitter) => submitter || form.querySelector('[type="submit"]');

  const setLoading = (form, button) => {
    form.dataset.aifSubmitting = 'true';
    form.setAttribute('aria-busy', 'true');

    const status = form.querySelector('[data-aif-submit-status]');
    if (status) {
      status.textContent = form.dataset.aifSubmitStatus || 'Envoi en cours, veuillez patienter.';
    }

    if (!button) {
      return;
    }

    // aria-disabled plutôt que disabled : le bouton garde le focus et son style.
    button.setAttribute('aria-disabled', 'true');
    button.classList.add('aif-button--loading');

    const label = button.querySelector('[data-aif-button-label]');
    if (label && button.dataset.aifLoadingLabel) {
      label.dataset.aifDefaultLabel = label.textContent;
      label.textContent = button.dataset.aifLoadingLabel;
    }
  };

  const reset = (form) => {
    delete form.dataset.aifSubmitting;
    form.removeAttribute('aria-busy');

    const status = form.querySelector('[data-aif-submit-status]');
    if (status) {
      status.textContent = '';
    }

    const button = getButton(form, null);
    if (!button) {
      return;
    }

    button.removeAttribute('aria-disabled');
    button.classList.remove('aif-button--loading');

    const label = button.querySelector('[data-aif-button-label]');
    if (label && label.dataset.aifDefaultLabel) {
      label.textContent = label.dataset.aifDefaultLabel;
    }
  };

  forms.forEach((form) => {
    // L'événement submit n'est émis qu'une fois la validation native passée.
    form.addEventListener('submit', (event) => {
      if (form.dataset.aifSubmitting === 'true') {
        event.preventDefault();
        return;
      }

      setLoading(form, getButton(form, event.submitter));
    });
  });

  // Retour arrière depuis la page de confirmation : la page restaurée depuis le
  // cache du navigateur ne doit pas rester bloquée en chargement.
  window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
      forms.forEach(reset);
    }
  });
})();
