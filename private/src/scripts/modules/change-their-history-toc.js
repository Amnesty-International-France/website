const changeTheirHistoryToc = () => {
  const tocBlocks = document.querySelectorAll('.change-their-history-toc-block');
  if (!tocBlocks.length) {
    return;
  }

  const content = document.querySelector('.page-content, .article-content');
  if (!content) {
    return;
  }

  const headings = Array.from(content.querySelectorAll('h2'));
  if (!headings.length) {
    tocBlocks.forEach((block) => {
      block.classList.add('is-empty');
    });
    return;
  }

  tocBlocks.forEach((block, blockIndex) => {
    const list = block.querySelector('[data-change-their-history-toc-list]');
    if (!list) {
      return;
    }

    list.innerHTML = '';
    headings.forEach((heading, index) => {
      const headingId = heading.id || `clh-${blockIndex + 1}-${index + 1}`;
      if (!heading.id) {
        heading.setAttribute('id', headingId);
      }
      const text = heading.textContent?.trim();
      if (!text) {
        return;
      }
      const li = document.createElement('li');
      const link = document.createElement('a');
      link.textContent = text;
      link.href = `#${headingId}`;
      li.appendChild(link);
      list.appendChild(li);
    });
  });
};

export default changeTheirHistoryToc;
