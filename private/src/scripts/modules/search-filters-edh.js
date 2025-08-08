const edhFilters = () => {
  const filtersForm = document.querySelector('.edh-filters-form');

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

    const queryString = params.toString();

    window.location.href = `${filtersForm.getAttribute('action')}?${queryString}`;
  };

  filtersForm.addEventListener('submit', handleSubmit);
};

export default edhFilters;
