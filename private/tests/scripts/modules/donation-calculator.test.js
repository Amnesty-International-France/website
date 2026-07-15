import { describe, expect, it } from 'vitest';
import { calculator } from '../../../src/scripts/modules/donation-calculator';

const dispatchInput = (input, value) => {
  input.value = value;
  input.dispatchEvent(new Event('input', { bubbles: true }));
};

const dispatchClick = (element) => {
  element.dispatchEvent(new Event('click', { bubbles: true }));
};

describe('donation calculator - simple (no tabs)', () => {
  const buildSimpleCalculator = (rate) => {
    document.body.innerHTML = `
      <div class="donation-calculator" data-rate="${rate}">
        <a class="donation-link" href="/faire-un-don">Je donne</a>
        <input id="input-donation">
        <h4 id="donation-simulated"></h4>
      </div>
    `;

    return {
      calc: document.querySelector('.donation-calculator'),
      input: document.querySelector('#input-donation'),
      link: document.querySelector('.donation-link'),
      simulatedText: document.querySelector('h4#donation-simulated'),
    };
  };

  it('computes the after-tax-reduction amount and updates the link in cents, without a frequency param', () => {
    const { input, link, simulatedText } = buildSimpleCalculator(66);
    calculator();

    dispatchInput(input, '15');

    expect(simulatedText.textContent).toContain('5,1 €');
    expect(simulatedText.textContent).not.toContain('/mois');

    const url = new URL(link.href);
    expect(url.searchParams.get('amount')).toBe('1500');
    expect(url.searchParams.has('frequency')).toBe(false);
    expect(link.dataset.type).toBe('ponctuel');
    expect(link.dataset.amount).toBe('15');
  });

  it('strips only a single trailing zero from the formatted amount (not all of them)', () => {
    const { input, simulatedText } = buildSimpleCalculator(66);
    calculator();

    dispatchInput(input, '100');

    // 100 - 66% = 34.00 -> trailing "0" stripped once -> "34,0" (not "34,00" nor "34,")
    expect(simulatedText.textContent).toContain('34,0 €');
  });

  it('applies the 75% rate carried on the block data-rate attribute', () => {
    const { input, simulatedText } = buildSimpleCalculator(75);
    calculator();

    dispatchInput(input, '200');

    expect(simulatedText.textContent).toContain('50,0 €');
  });

  it('falls back to 0 when the input is cleared', () => {
    const { input, simulatedText } = buildSimpleCalculator(66);
    calculator();

    dispatchInput(input, '');

    // 0 - 66% = 0.00 -> trailing "0" stripped once -> "0,0"
    expect(simulatedText.textContent).toContain('0,0 €');
  });
});

describe('donation calculator - tabbed (punctual / monthly)', () => {
  const buildTabbedCalculator = () => {
    document.body.innerHTML = `
      <div class="donation-calculator" data-rate="66">
        <a class="donation-link" href="/faire-un-don">Je donne</a>
        <div class="donation-tabs">
          <div id="punctual" class="active">Ponctuel</div>
          <div id="monthly">Mensuel</div>
        </div>
        <div class="donation-body">
          <div id="punctual" class="amount-punctual active">
            <div class="don-radio"><input type="radio" name="amount-punctual" value="80"></div>
            <div class="don-radio"><input type="radio" name="amount-punctual" value="100" checked></div>
            <div class="don-radio"><input type="radio" name="amount-punctual" value="250"></div>
          </div>
          <div id="monthly" class="amount-monthly">
            <div class="don-radio"><input type="radio" name="amount-monthly" value="8"></div>
            <div class="don-radio"><input type="radio" name="amount-monthly" value="10" checked></div>
            <div class="don-radio"><input type="radio" name="amount-monthly" value="15"></div>
          </div>
        </div>
        <input id="input-donation">
        <h4 id="donation-simulated"></h4>
      </div>
    `;

    return {
      link: document.querySelector('.donation-link'),
      freeInput: document.querySelector('#input-donation'),
      simulatedText: document.querySelector('h4#donation-simulated'),
      punctualRadios: document.querySelectorAll('.amount-punctual .don-radio'),
    };
  };

  it('defaults to the checked radio of the initially active tab and tags the link accordingly', () => {
    const { link, freeInput, simulatedText } = buildTabbedCalculator();

    calculator();

    // 100 - 66% = 34.00 -> "34,0"
    expect(simulatedText.textContent).toContain('34,0 €');
    expect(freeInput.value).toBe('100');
    expect(link.dataset.type).toBe('ponctuel');
    expect(link.dataset.amount).toBe('100');

    const url = new URL(link.href);
    expect(url.searchParams.get('frequency')).toBe('once');
  });

  it('switches amount and unchecks radios when typing in the free input', () => {
    const { freeInput, simulatedText, punctualRadios } = buildTabbedCalculator();
    calculator();

    dispatchInput(freeInput, '42');

    expect(simulatedText.textContent).toContain('14,28 €');
    punctualRadios.forEach((container) => {
      expect(container.classList.contains('active')).toBe(false);
      expect(container.querySelector('input').checked).toBe(false);
    });
  });

  it('updates the amount and link when a different radio is clicked', () => {
    const { link, freeInput } = buildTabbedCalculator();
    calculator();

    const radio250 = document.querySelector('.amount-punctual input[value="250"]');
    dispatchClick(radio250.closest('.don-radio'));

    expect(radio250.checked).toBe(true);
    expect(freeInput.value).toBe('250');
    expect(link.dataset.amount).toBe('250');

    const url = new URL(link.href);
    expect(url.searchParams.get('amount')).toBe('25000');
  });

  it('always sets a frequency query param, unlike the simple (non-tabbed) calculator', () => {
    const { link } = buildTabbedCalculator();
    calculator();

    const url = new URL(link.href);
    expect(url.searchParams.has('frequency')).toBe(true);
  });
});
