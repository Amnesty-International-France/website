/**
 * Only the data the editor preview needs is kept from the REST `_embedded` payload:
 * storing it whole put tens of KB of free text (EXIF, excerpts, related posts) in the
 * homepage content, which the WAF ended up blocking on autosave (MAINT-307).
 */

export const extractAllCustomTerms = (embeddedData) => {
  if (!embeddedData || !Array.isArray(embeddedData['wp:term'])) {
    return [];
  }

  return embeddedData['wp:term']
    .filter(Array.isArray)
    .flat()
    .filter((term) => term.taxonomy !== 'category' && term.taxonomy !== 'post_tag')
    .map(({ id, name, slug, taxonomy }) => ({ id, name, slug, taxonomy }));
};

export const chipFromEmbedded = (embeddedData) => {
  const term = embeddedData?.['wp:term']?.[0]?.[0];
  const label = term?.acf?.category_singular_name || '';
  const slug = term?.slug || '';

  return label && slug ? { label, slug } : null;
};

export const previewFromEmbedded = (embeddedData) => ({
  selectedPostImageUrl: embeddedData?.['wp:featuredmedia']?.[0]?.source_url || '',
  selectedPostChip: chipFromEmbedded(embeddedData),
});

const hasEmbedded = (item) => Boolean(item) && Object.hasOwn(item, '_embedded');

export const hasEmbeddedData = (items) => Array.isArray(items) && items.some(hasEmbedded);

export const stripEmbeddedData = (items) =>
  items.map((item) => {
    if (!hasEmbedded(item)) {
      return item;
    }

    const { _embedded: embeddedData, ...rest } = item;

    return { ...rest, ...previewFromEmbedded(embeddedData) };
  });

// Block deprecation: a valid dynamic block only migrates when `isEligible` opts it in.
export const embeddedDataDeprecation = {
  isEligible: (attributes) => hasEmbeddedData(attributes?.items),
  migrate: (attributes) => ({ ...attributes, items: stripEmbeddedData(attributes.items) }),
};
