const ChipCategory = ({ item }) => {
  let label = '';
  let slug = '';

  const category = item?._embedded?.['wp:term']?.[0]?.[0];

  if (category) {
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
