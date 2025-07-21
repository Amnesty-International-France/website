const ibanInput = document.getElementById('iban');

if (ibanInput) {
  ibanInput.addEventListener('input', function () {
    const iban = this.value.replace(/\s+/g, ''); // Supprimer les espaces
    const formattedIban = formatIban(iban);
    this.value = formattedIban;
  });

  function formatIban(iban) {
    if (!iban) {
      return '';
    }
    return iban.match(/.{1,4}/g).join(' '); // Ajouter un espace tous les 4 caractères
  }
}


