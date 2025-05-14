const tableOfContents = () => {
  const content = document.querySelector('.article-content');
  const tocList = document.getElementById('toc-list');
  const tocButton = document.getElementById('toc-button');
  const tocDropdown = document.getElementById('toc-dropdown');
  const tocIconClosed = document.getElementById('toc-icon-closed');
  const tocIconOpen = document.getElementById('toc-icon-open');

  if (!content || !tocList || !tocButton || !tocDropdown || !tocIconClosed || !tocIconOpen) return;

  const headings = content.querySelectorAll('h2');

  headings.forEach((heading, index) => {
    // eslint-disable-next-line no-param-reassign
    if (!heading.id) heading.id = `sommaire-${index + 1}`;
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.textContent = heading.textContent;
    a.href = `#${heading.id}`;
    li.appendChild(a);
    tocList.appendChild(li);
  });

  tocButton.addEventListener('click', () => {
    tocDropdown.classList.toggle('visible');

    tocIconClosed.style.display = tocDropdown.classList.contains('visible') ? 'none' : 'block';
    tocIconOpen.style.display = tocDropdown.classList.contains('visible') ? 'block' : 'none';
  });
};

export default tableOfContents;
