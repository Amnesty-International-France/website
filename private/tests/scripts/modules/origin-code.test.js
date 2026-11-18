import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { keepOriginCode, STORAGE_KEY } from '../../../src/scripts/modules/origin-code';

const DONATION_URL =
  'https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=WBF01W1012&amount=1500&frequency=regular';

const setLocation = (search) => {
  window.history.replaceState({}, '', `/page${search}`);
};

const render = () => {
  document.body.innerHTML = `
    <a id="header" href="${DONATION_URL}">Faire un don</a>
    <a id="popin" href="https://soutenir.amnesty.fr/b?cid=466&lang=fr_FR">Don pop-in</a>
    <a id="other" href="https://www.amnesty.fr/petitions?reserved_originecode=PET01">Pétitions</a>
    <a id="relative" href="/agir">Agir</a>
  `;
};

const originCodeOf = (id) =>
  new URL(document.getElementById(id).href).searchParams.get('reserved_originecode');

describe('origin code kept along the visit', () => {
  let stop = () => {};

  beforeEach(() => {
    window.sessionStorage.clear();
    render();
  });

  afterEach(() => {
    stop();
    setLocation('');
  });

  it('replaces the origin code of donation links with the one from the URL', () => {
    setLocation('?reserved_originecode=EMAIL2011');
    stop = keepOriginCode();

    expect(originCodeOf('header')).toBe('EMAIL2011');
    expect(originCodeOf('popin')).toBe('EMAIL2011');
  });

  it('keeps the other parameters of donation links', () => {
    setLocation('?reserved_originecode=EMAIL2011');
    stop = keepOriginCode();

    const url = new URL(document.getElementById('header').href);
    expect(url.searchParams.get('cid')).toBe('66');
    expect(url.searchParams.get('amount')).toBe('1500');
    expect(url.searchParams.get('frequency')).toBe('regular');
  });

  it('does not touch links outside the donation site', () => {
    setLocation('?reserved_originecode=EMAIL2011');
    stop = keepOriginCode();

    expect(originCodeOf('other')).toBe('PET01');
    expect(document.getElementById('relative').getAttribute('href')).toBe('/agir');
  });

  it('applies the code stored earlier in the session on a page without code in the URL', () => {
    setLocation('?reserved_originecode=EMAIL2011');
    stop = keepOriginCode();
    stop();

    render();
    setLocation('');
    stop = keepOriginCode();

    expect(window.sessionStorage.getItem(STORAGE_KEY)).toBe('EMAIL2011');
    expect(originCodeOf('header')).toBe('EMAIL2011');
  });

  it('accepts the code_origine alias', () => {
    setLocation('?code_origine=NL2011');
    stop = keepOriginCode();

    expect(originCodeOf('header')).toBe('NL2011');
  });

  it('lets a newer code in the URL replace the stored one', () => {
    window.sessionStorage.setItem(STORAGE_KEY, 'OLD01');
    setLocation('?reserved_originecode=NEW02');
    stop = keepOriginCode();

    expect(window.sessionStorage.getItem(STORAGE_KEY)).toBe('NEW02');
    expect(originCodeOf('header')).toBe('NEW02');
  });

  it('leaves the links unchanged without any code', () => {
    setLocation('');
    stop = keepOriginCode();

    expect(document.getElementById('header').href).toBe(DONATION_URL);
  });

  it('ignores a malformed code', () => {
    setLocation('?reserved_originecode=%3Cscript%3E');
    stop = keepOriginCode();

    expect(window.sessionStorage.getItem(STORAGE_KEY)).toBeNull();
    expect(document.getElementById('header').href).toBe(DONATION_URL);
  });

  it('applies the code to a link whose href changed after the page load, when clicked', () => {
    setLocation('?reserved_originecode=CLICK99');
    stop = keepOriginCode();

    const link = document.getElementById('header');
    link.href = 'https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=WBF01W1012&amount=8000';
    link.addEventListener('click', (event) => event.preventDefault());
    link.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

    expect(originCodeOf('header')).toBe('CLICK99');
    expect(new URL(link.href).searchParams.get('amount')).toBe('8000');
  });

  it('stops rewriting links on click once stopped', () => {
    setLocation('?reserved_originecode=CLICK99');
    stop = keepOriginCode();
    stop();

    const link = document.getElementById('header');
    link.href = DONATION_URL;
    link.addEventListener('click', (event) => event.preventDefault());
    link.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

    expect(link.href).toBe(DONATION_URL);
  });
});
