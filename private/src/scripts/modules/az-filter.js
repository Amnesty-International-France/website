const azFilter = () => {
  const letters = document.querySelectorAll('.az-letter');
  const items = document.querySelectorAll('.country');
  const display = document.querySelector('.az-letter-display');

  if (!letters.length || !display || !items.length) return;

  const filterByLetter = (letter) => {
    display.textContent = letter;

    items.forEach((el) => {
      const element = el;
      element.style.display = el.dataset.letter === letter ? 'block' : 'none';
    });
  };

  letters.forEach((button) => {
    button.addEventListener('click', function () {
      const selectedLetter = this.dataset.letter;

      letters.forEach((btn) => btn.classList.remove('active'));
      this.classList.add('active');

      filterByLetter(selectedLetter);
    });
  });

  filterByLetter('A');
};

export default azFilter;
