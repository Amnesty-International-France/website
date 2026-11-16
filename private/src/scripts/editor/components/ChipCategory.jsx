const ChipCategory = ({ item }) => {
  let label = '';
  let slug = '';

  // Items of the articles-homepage block store their chip; events still come with `_embedded`.
  const storedChip = item?.selectedPostChip;
  const category = item?._embedded?.['wp:term']?.[0]?.[0];

  if (storedChip) {
    ({ label, slug } = storedChip);
  } else if (category) {
    label = category?.acf?.category_singular_name || '';
    slug = category?.slug || '';
  } else if (item?.category === 'landmark') {
    label = 'Repère';
    slug = 'reperes';
  }

  if (!label || !slug) return null;

  let styleClass = '';
  switch (slug) {
    case 'actualites':
    case 'chronique':
    case 'chroniques':
    case 'reperes':
      styleClass = 'bg-yellow';
      break;
    case 'dossiers':
    case 'campagnes':
      styleClass = 'bg-black';
      break;
    default:
      styleClass = 'outline-black';
      break;
  }

  return (
    <div className={`chip-category ${styleClass}`}>
      <span className="chip-label">{label}</span>
    </div>
  );
};

export default ChipCategory;
