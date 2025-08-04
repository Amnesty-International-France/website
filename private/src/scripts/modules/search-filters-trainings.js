const trainingFilters = () => {
  const filtersForm = document.querySelector('.training-filters-form');

  if (!filtersForm) {
    return;
  }

  const handleSubmit = (event) => {
    event.preventDefault();

    const formData = new FormData(filtersForm);
    const params = new URLSearchParams();

    Array.from(formData.entries()).forEach(([key, value]) => {
      if (value) {
        params.append(key, value);
      }
    });

    if (!params.has('qyear') && params.has('qmonth')) {
      params.delete('qmonth');
    }

    const queryString = params.toString();
    const targetUrl = `${filtersForm.getAttribute('action')}?${queryString}`;

    window.location.href = targetUrl;
  };

  filtersForm.addEventListener('submit', handleSubmit);
};

export default trainingFilters;
