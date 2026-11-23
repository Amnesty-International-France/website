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

  // Empêche un double envoi (double-clic) pendant l'appel à Salesforce.
  ibanInput.form?.addEventListener('submit', () => {
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.dataset.locked = '1';
    }
  });

  ibanInput.form?.addEventListener('reset', () => {
    // La valeur du champ est restaurée après l'événement reset.
    setTimeout(updateSubmitState, 0);
  });

  updateSubmitState();
}
