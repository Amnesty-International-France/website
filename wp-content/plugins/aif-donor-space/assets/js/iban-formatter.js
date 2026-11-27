const ibanInput = document.getElementById('iban');

if (ibanInput) {
  const submitButton = document.getElementById('iban-submit');
  const normalize = (value) => value.replace(/\s+/g, '').toUpperCase();
  const initialIban = normalize(ibanInput.dataset.initialIban || '');

  const formatIban = (iban) => {
    if (!iban) {
      return '';
    }
    return iban.match(/.{1,4}/g).join(' '); // Ajouter un espace tous les 4 caractères
  };

  // Le bouton n'est cliquable que si l'IBAN a été modifié (ou verrouillé côté serveur).
  const updateSubmitState = () => {
    if (!submitButton || submitButton.dataset.locked === '1') {
      return;
    }
    const current = normalize(ibanInput.value);
    submitButton.disabled = current === '' || current === initialIban;
  };

  ibanInput.addEventListener('input', () => {
    ibanInput.value = formatIban(normalize(ibanInput.value));
    updateSubmitState();
  });

  ibanInput.form?.addEventListener('reset', () => {
    // La valeur du champ est restaurée après l'événement reset.
    setTimeout(updateSubmitState, 0);
  });

  // Après une erreur, le serveur renvoie la saisie du donateur : le champ la reprend,
  // et « Annuler » restaure l'IBAN actuel (valeur du champ).
  if (ibanInput.dataset.submittedIban) {
    ibanInput.value = ibanInput.dataset.submittedIban;
  }

  // Retour arrière : la page restaurée depuis le cache du navigateur garde la saisie envoyée.
  // Le champ reprend l'IBAN actuel, le serveur dira au prochain chargement si une demande est en cours.
  window.addEventListener('pageshow', (event) => {
    if (event.persisted && ibanInput.form) {
      ibanInput.form.reset();
    }
  });

  updateSubmitState();
}
