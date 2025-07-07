const elementTextContent = (element, isMonthly = false, amount = null) => {
  const el = document.querySelector(element);
  const textWithAmountValue = amount ? `${amount} €` : '€';

  el.innerHTML = '';

  const textNode = document.createTextNode(textWithAmountValue);
  el.appendChild(textNode);

  if (isMonthly) {
    const span = document.createElement('span');
    span.textContent = ' /mois';
    el.appendChild(span);
  }
};

const taxDeductionCalculation = (amount) => {
  const finalPrice = amount - amount * 0.66;
  return Number(finalPrice).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const changeAmountValue = (amount, isMonthly = false) => {
  const donationSimulated = document.getElementById('donation-simulated');

  if (donationSimulated && donationSimulated.tagName === 'INPUT') donationSimulated.value = amount;

  if (donationSimulated && donationSimulated.tagName === 'P')
    elementTextContent('#donation-simulated', isMonthly, amount);
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
  const donationInputEntrance = donationBody.querySelector('#input-donation');

  if (donationInputEntrance) {
    donationInputEntrance.addEventListener('input', (event) => {
      const getPriceWithoutTax = taxDeductionCalculation(event.currentTarget.value);

      changeAmountValue(getPriceWithoutTax);
      addAmountOnDonationLink(getPriceWithoutTax);
    });
  }
};

export const selectedAmount = () => {
  const bodyActive = document.querySelector('.donation-body div[class*="amount-"].active');

  if (bodyActive) {
    const allRadioInput = bodyActive.querySelectorAll('.don-radio');
    const checkedInput = bodyActive.querySelector('input[type="radio"]:checked');
    const isMonthly = bodyActive.id === 'monthly';

    changeAmountValue(taxDeductionCalculation(checkedInput.value), isMonthly);
    addAmountOnDonationLink(checkedInput.value);

    allRadioInput.forEach((radioDiv) => {
      radioDiv.addEventListener('click', () => {
        const input = radioDiv.querySelector('input[type="radio"]');
        if (input) {
          input.checked = true;
          allRadioInput.forEach((div) => {
            div.classList.remove('active');
          });
          radioDiv.classList.add('active');
          changeAmountValue(taxDeductionCalculation(input.value), isMonthly);
          addAmountOnDonationLink(input.value);
        }
      });
    });
  }
};

export const selectedTab = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const getDonationTabs = document.querySelector('.donation-tabs');
    const getBody = document.getElementById('donation-body');

    if (getDonationTabs && getBody) {
      const allTabsCalculator = getDonationTabs.children;
      const allBodyItem = getBody.children;
      let activeTab = getBody.querySelector('div[class*="amount-"].active');
      const isMonthly = activeTab.id === 'monthly';

      elementTextContent('#amount-total', isMonthly);

      getDonationTabs.addEventListener('click', (event) => {
        const clickedCurrentTab = event.target;

        const currentIndex = [...allTabsCalculator].indexOf(clickedCurrentTab);
        if (currentIndex === -1) return;

        [...allTabsCalculator].forEach((tab, index) => {
          const bodyItem = allBodyItem[index];
          if (index === currentIndex) {
            if (tab.classList.contains('hidden')) {
              tab.classList.replace('hidden', 'active');
              if (bodyItem.classList.contains('hidden')) {
                bodyItem.classList.replace('hidden', 'active');
              }
            }
          } else {
            tab.classList.replace('active', 'hidden');
            bodyItem.classList.replace('active', 'hidden');
          }
        });

        activeTab = allTabsCalculator[currentIndex];
        const currentActiveTabsIsMonthly = activeTab.id === 'monthly';
        elementTextContent('#amount-total', currentActiveTabsIsMonthly);
        selectedAmount();
      });
    }
  });
};

export default {
  donationCalculator,
  selectedAmount,
  selectedTab,
};
