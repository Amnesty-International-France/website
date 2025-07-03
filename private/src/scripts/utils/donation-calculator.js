const taxDeductionCalculation = (amount) => {
  const finalPrice = amount - amount * 0.66;

  return Number(finalPrice).toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const changeAmountValue = (amount) => {
  const amountInputUpdate = document.getElementById('input-donation-simulated');
  if (amountInputUpdate) {
    amountInputUpdate.value = amount;
  }
};

const addAmountOnDonationLink = (amount) => {
  const donationLink = document.querySelector('.donation-link');

  const query = new URLSearchParams({
    cid: 252,
    lang: 'fr_FR',
    reserved_origincode: 'WBF01W1005',
    amount,
  });

  donationLink.href = `${donationLink.href}?${query.toString()}`;
};

export const donationCalculator = () => {
  const donationBody = document.querySelector('.donation-body');
  const donationInputEntrance = donationBody.querySelector('.input-donation');

  if (donationInputEntrance) {
    donationInputEntrance.addEventListener('input', (event) => {
      const getPriceWithoutTax = taxDeductionCalculation(event.currentTarget.value);

      setTimeout(async () => {
        changeAmountValue(getPriceWithoutTax);
      }, 300);
      addAmountOnDonationLink(event.currentTarget.value);
    });
  }
};

export default {
  donationCalculator,
};
