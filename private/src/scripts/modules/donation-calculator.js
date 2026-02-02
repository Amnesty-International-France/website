const tagLink = (calc, amount, isMonthly) => {
  if (!calc || Number.isNaN(amount)) return;

  const link = calc.querySelector('.donation-link');
  const type = isMonthly ? 'mensuel' : 'ponctuel';
  link.setAttribute('data-type', type);
  link.setAttribute('data-amount', `${amount}`);
};

const taxDeductionCalculation = (amount, rate) => {
  const finalPrice = amount - (amount * parseInt(rate, 10)) / 100;
  let priceAsString = finalPrice.toFixed(2);
  if (priceAsString.endsWith('0')) {
    priceAsString = priceAsString.slice(0, -1);
  }
  return priceAsString.replace('.', ',');
};

const updateCalculatorUI = (calculator, amount, isMonthly) => {
  if (!calculator || Number.isNaN(amount)) return;

  const rate = parseInt(calculator.dataset.rate, 10);
  const finalAmount = taxDeductionCalculation(amount, rate);
  const link = calculator.querySelector('.donation-link');

  const donationSimulatedText = calculator.querySelector('h4#donation-simulated');
  if (donationSimulatedText) {
    donationSimulatedText.innerHTML = `${finalAmount} € ${isMonthly ? '<span>/mois</span>' : ''}`;
  }

  const donationSimulatedInput = calculator.querySelector('input#donation-simulated');
  if (donationSimulatedInput) {
    donationSimulatedInput.value = finalAmount;
  }

  if (link) {
    const url = new URL(link.href);
    url.searchParams.set('amount', (amount * 100).toString());
    if (isMonthly !== null) {
      const freq = isMonthly ? 'regular' : 'once';
      url.searchParams.set('frequency', freq);
    }
    link.href = url.toString();
  }
};

const initializeSimpleCalculator = (calculator) => {
  tagLink(calculator, 0, null);
  const input = calculator.querySelector('#input-donation');
  if (!input) return;

  input.addEventListener('input', () => {
    const amount = parseFloat(input.value) || 0;
    updateCalculatorUI(calculator, amount, null);
    tagLink(calculator, amount, null);
  });
};

const initializeTabbedCalculator = (calculator) => {
  const tabsContainer = calculator.querySelector('.donation-tabs');
  const bodyContainer = calculator.querySelector('.donation-body');
  const freeInput = calculator.querySelector('#input-donation');
  const currencyPunctual = calculator.querySelector('.currency-punctual');
  const currencyMonthly = calculator.querySelector('.currency-monthly');
  tagLink(calculator, 15, true);

  const setupListenersForActiveTab = () => {
    const activeBody = bodyContainer.querySelector('div[class*="amount-"].active');
    if (!activeBody) return;

    const isMonthly = activeBody.classList.contains('amount-monthly');
    const radioContainers = activeBody.querySelectorAll('.don-radio');

    freeInput.addEventListener('input', () => {
      radioContainers.forEach((container) => {
        container.classList.remove('active');
        const radio = container.querySelector('input[type="radio"]');
        if (radio) radio.checked = false;
      });
      const amount = parseFloat(freeInput.value) || 0;
      updateCalculatorUI(calculator, amount, isMonthly);
      tagLink(calculator, amount, isMonthly);
    });

    radioContainers.forEach((container) => {
      container.addEventListener('click', () => {
        radioContainers.forEach((c) => c.classList.remove('active'));
        container.classList.add('active');

        const radio = container.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
          const amount = parseFloat(radio.value);
          freeInput.value = amount;
          updateCalculatorUI(calculator, amount, isMonthly);
          tagLink(calculator, amount, isMonthly);
        }
      });
    });

    const checkedRadio = activeBody.querySelector('input[type="radio"]:checked');
    if (checkedRadio) {
      const amount = parseFloat(checkedRadio.value);
      freeInput.value = amount;
      updateCalculatorUI(calculator, amount, isMonthly);
      tagLink(calculator, amount, isMonthly);
    } else {
      const amount = parseFloat(freeInput.value) || 0;
      updateCalculatorUI(calculator, amount, isMonthly);
      tagLink(calculator, amount, isMonthly);
    }
  };

  tabsContainer.addEventListener('click', (event) => {
    const clickedTab = event.target.closest('div[id]');
    if (!clickedTab || clickedTab.classList.contains('active')) return;

    const targetId = clickedTab.id;
    const allTabs = tabsContainer.querySelectorAll('div[id]');
    const allBodies = bodyContainer.querySelectorAll('div[class*="amount-"]');

    if (currencyPunctual && currencyMonthly) {
      const isNowMonthly = targetId === 'monthly';
      currencyMonthly.classList.toggle('hidden-currency', !isNowMonthly);
      currencyPunctual.classList.toggle('hidden-currency', isNowMonthly);
    }

    allTabs.forEach((tab) => {
      const isTarget = tab.id === targetId;
      tab.classList.toggle('active', isTarget);
      tab.classList.toggle('hidden', !isTarget);
    });

    allBodies.forEach((body) => {
      const isTarget = body.id === targetId;
      body.classList.toggle('active', isTarget);
      body.classList.toggle('hidden', !isTarget);
    });

    setupListenersForActiveTab();
  });

  setupListenersForActiveTab();
};

export const calculator = () => {
  const calculators = document.querySelectorAll('.donation-calculator');
  calculators.forEach((calc) => {
    if (calc.querySelector('.donation-tabs')) {
      initializeTabbedCalculator(calc);
    } else {
      initializeSimpleCalculator(calc);
    }
  });
};

export const hoverDonationMenu = () => {
  const donateButton = document.querySelector('.donate-button-desktop');
  if (!donateButton) return;

  const calculatorElement = donateButton.querySelector('.nav-don-calculator');
  if (!calculatorElement) return;

  let isHover = false;
  const hide = () => {
    setTimeout(() => {
      if (!isHover) {
        calculatorElement.style.visibility = 'hidden';
      }
    }, 100);
  };
  const show = () => {
    isHover = true;
    calculatorElement.style.visibility = 'visible';
  };
  const setHover = (val) => () => {
    isHover = val;
  };

  donateButton.addEventListener('mouseenter', show);
  calculatorElement.addEventListener('mouseenter', setHover(true));
  calculatorElement.addEventListener('mouseleave', () => {
    isHover = false;
    hide();
  });
  donateButton.addEventListener('mouseleave', () => {
    isHover = false;
    hide();
  });
};

export default {
  calculator,
  hoverDonationMenu,
};
