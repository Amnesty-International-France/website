const taxDeductionCalculation = (amount) => {
  const finalPrice = amount - amount * 0.66;

  return `${Number(finalPrice).toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })} €`;
};

const changeAmountValue = (amount) => {
  const amountTextUpdate = document.getElementById('price-simulated');
  const span = amountTextUpdate.querySelector('span');
  amountTextUpdate.innerHTML = '';
  amountTextUpdate.append(taxDeductionCalculation(amount));
  amountTextUpdate.append(span);
};

export const donationCalculator = () => {
  const bodyActive = document.querySelector('div[class*="amount-"].active');

  const allRadioInput = bodyActive.querySelectorAll('.don-radio');
  const checkedInput = bodyActive.querySelector('input[type="radio"]:checked');
  changeAmountValue(checkedInput.value);

  allRadioInput.forEach((radioDiv) => {
    radioDiv.addEventListener('click', () => {
      const input = radioDiv.querySelector('input[type="radio"]');
      if (input) {
        input.checked = true;
        allRadioInput.forEach((div) => {
          div.classList.remove('active');
        });
        radioDiv.classList.add('active');
        changeAmountValue(input.value);
      }
    });
  });

  const freeAmountEntry = bodyActive.querySelector('input[class="input-don-free"]');
  freeAmountEntry.addEventListener('input', (event) => {
    const inputValue = event.currentTarget.value;
    changeAmountValue(inputValue);

    if (inputValue === '') {
      changeAmountValue(checkedInput.value);
    }
  });
};
export const selectedTab = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const getDonationHeader = document.getElementById('donation-header');
    const getBodyChildren = document.getElementById('donation-body');

    const allTabsCalculator = getDonationHeader.children;
    const allBodyItem = getBodyChildren.children;

    getDonationHeader.addEventListener('click', (event) => {
      const clickedCurrentTab = event.target;
      let currentIndex;
      [...allTabsCalculator].forEach((tab, index) => {
        if (tab === clickedCurrentTab) currentIndex = index;

        if (tab === clickedCurrentTab && clickedCurrentTab.classList.contains('hidden')) {
          tab.classList.replace('hidden', 'active');

          if (allBodyItem[currentIndex].classList.contains('hidden'))
            allBodyItem[currentIndex].classList.replace('hidden', 'active');
        } else {
          tab.classList.replace('active', 'hidden');
          allBodyItem[index].classList.replace('active', 'hidden');
        }
      });
      donationCalculator();
    });
  });
};

export default {
  donationCalculator,
  selectedTab,
};
