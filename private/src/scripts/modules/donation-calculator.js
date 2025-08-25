const elementTextContent = (calculator, selector, isMonthly = false, amount = null) => {
  const el = calculator.querySelector(selector);

  if (!el) {
    console.error(`elementTextContent: Element "${selector}" not found.`);
    return;
  }

  const amountText = amount ? `${amount} €` : '€';
  const monthlyText = isMonthly ? ' <span>/mois</span>' : '';

  el.innerHTML = `${amountText}${monthlyText}`;
};

const taxDeductionCalculation = (amount, rate) => {
  const finalPrice = amount - (amount * parseInt(rate, 10)) / 100;

  return Number(finalPrice).toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
};

const changeAmountValue = (calculator, amount, isMonthly = false) => {
  if (!calculator) return;

  const donationSimulated = calculator.querySelector('#donation-simulated');

  if (donationSimulated && donationSimulated.tagName === 'INPUT') donationSimulated.value = amount;

  if (donationSimulated && donationSimulated.tagName === 'P')
    elementTextContent(calculator, '#donation-simulated', isMonthly, amount);
};

const addAmountOnDonationLink = (calculator, amount, frequency = null) => {
  if (!calculator) return;

  const link = calculator.querySelector('.donation-link');
  if (!link) return;

  const url = new URL(link.href);
  url.searchParams.set('amount', (amount * 100).toString());

  if (frequency) {
    const freq = frequency === 'monthly' ? 'regular' : 'once';
    url.searchParams.set('frequency', freq);
  }

  link.href = url.toString();
};

const donationCalculator = (calculator) => {
  if (!calculator) return;

  const bodyActive = calculator.querySelector('.donation-body div[class*="amount-"].active');
  const isMonthly = (bodyActive && bodyActive.id === 'monthly') ?? false;
  const rate = parseInt(calculator.attributes['data-rate'].value, 10);
  const input = calculator.querySelector('.donation-body #input-donation');

  if (!input) return;

  input.addEventListener('input', (event) => {
    const value = event.currentTarget.value;
    const amount = taxDeductionCalculation(value, rate);

    changeAmountValue(calculator, amount, isMonthly);
    addAmountOnDonationLink(calculator, value);
  });
};

const selectedAmount = (calculator, withTabs) => {
  if (!calculator) return;

  if (!withTabs) {
    donationCalculator(calculator);
    return;
  }

  const bodyActive = calculator.querySelector('.donation-body div[class*="amount-"].active');
  const allRadioInputs = bodyActive.querySelectorAll('.don-radio');
  const checkedInput = bodyActive.querySelector('input[type="radio"]:checked');
  const isMonthly = bodyActive.id === 'monthly';
  const rate = parseInt(calculator.attributes['data-rate'].value, 10);

  if (checkedInput) {
    elementTextContent(
      calculator,
      '#donation-simulated',
      isMonthly,
      taxDeductionCalculation(checkedInput.value, rate),
    );
    changeAmountValue(calculator, taxDeductionCalculation(checkedInput.value, rate), isMonthly);
    addAmountOnDonationLink(calculator, checkedInput.value);
  }

  bodyActive.addEventListener('click', (event) => {
    const radioBlock = event.target.closest('.don-radio');

    if (!radioBlock) return;

    const input = radioBlock.querySelector('input[type="radio"]');

    if (!input) return;

    allRadioInputs.forEach((block) => {
      const blockInput = block.querySelector('input[type="radio"]');

      block.classList.remove('active');
      blockInput.checked = false;
    });

    radioBlock.classList.add('active');
    input.checked = true;

    changeAmountValue(calculator, taxDeductionCalculation(input.value, rate), isMonthly);
    addAmountOnDonationLink(calculator, input.value);
    elementTextContent(
      calculator,
      '#donation-simulated',
      isMonthly,
      taxDeductionCalculation(input.value, rate),
    );
  });
};

const selectedTab = (calculator) => {
  if (!calculator) return;

  const rate = parseInt(calculator.attributes['data-rate'].value, 10);
  const tabContainer = calculator.querySelector('.donation-tabs');
  const bodyContainer = calculator.querySelector('#donation-body');

  if (!tabContainer || !bodyContainer) return;

  const tabs = Array.from(tabContainer.children);
  const bodies = Array.from(bodyContainer.children);
  const getActiveBody = () => bodyContainer.querySelector('div[class*="amount-"].active');

  const updateAmountDisplay = () => {
    const activeBody = getActiveBody();
    if (activeBody) {
      const isMonthly = activeBody.id === 'monthly';
      const defaultSelectedRadio = activeBody.querySelector('input[type="radio"]:checked');

      elementTextContent(calculator, '#amount-total', isMonthly);
      selectedAmount(calculator, true);
      donationCalculator(calculator);
      elementTextContent(
        calculator,
        '#donation-simulated',
        isMonthly,
        taxDeductionCalculation(defaultSelectedRadio.value, rate),
      );
    } else {
      console.warn('selectedTab: No active tab found.');
    }
  };

  updateAmountDisplay();

  tabContainer.addEventListener('click', (event) => {
    const clickedTab = event.target;
    const index = tabs.indexOf(clickedTab);
    if (index === -1) return;

    tabs.forEach((tab, i) => {
      const body = bodies[i];
      const isActive = i === index;

      tab.classList.toggle('active', isActive);
      tab.classList.toggle('hidden', !isActive);

      body.classList.toggle('active', isActive);
      body.classList.toggle('hidden', !isActive);
    });

    updateAmountDisplay();
  });
};

export const hoverDonationMenu = () => {
  const donateButton = document.querySelector('.donate-button-desktop');

  if (!donateButton) {
    return;
  }

  const calculator = donateButton.querySelector('.nav-don-calculator');

  if (!calculator) {
    return;
  }

  let isHover = false;

  donateButton.addEventListener('mouseenter', () => {
    isHover = true;
    calculator.style.visibility = 'visible';
  });

  calculator.addEventListener('mouseenter', () => {
    isHover = true;
  });

  calculator.addEventListener('mouseleave', () => {
    isHover = false;
    if (!isHover) {
      setTimeout(() => {
        calculator.style.visibility = 'hidden';
      }, 100);
    }
  });

  donateButton.addEventListener('mouseleave', () => {
    isHover = false;
    if (!isHover) {
      setTimeout(() => {
        calculator.style.visibility = 'hidden';
      }, 100);
    }
  });
};
export const calculator = () => {
  const calculators = document.querySelectorAll('.donation-calculator');

  if (!calculators) return;

  calculators.forEach((calc) => {
    selectedTab(calc);
    selectedAmount(calc);
    donationCalculator(calc);
  });
};

export default {
  calculator,
  hoverDonationMenu,
};
