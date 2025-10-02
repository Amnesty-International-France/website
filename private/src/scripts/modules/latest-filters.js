import { inputsToObject, objectToQueryString } from '../utils/url';

const handleFilterSubmit = (event) => {
  event.preventDefault();

  const [formTarget] = event.target.getAttribute('action').split('?');
  const inputs = Array.from(event.target.querySelectorAll('input,select'));
  const request = inputsToObject(inputs);

  window.location = [formTarget, objectToQueryString(request)].filter(Boolean).join('?');
};

export default function latestFilters() {
  const form = document.getElementById('filter-form');

  if (form) {
    form.addEventListener('submit', handleFilterSubmit);
  }
}
