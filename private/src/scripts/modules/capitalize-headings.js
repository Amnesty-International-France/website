export default function capitalizeFirstWord() {
  document.addEventListener('DOMContentLoaded', () => {
    const headings = document.querySelectorAll('.wp-block-heading');

    headings.forEach((el) => {
      let text = el.textContent.trim();

      if (!text) return;

      text = text.toLocaleLowerCase();

      const firstLetter = text[0].toLocaleUpperCase();

      // eslint-disable-next-line no-param-reassign
      el.textContent = firstLetter + text.slice(1);
    });
  });
}
