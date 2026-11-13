import { describe, expect, it } from 'vitest';
import {
  chipFromEmbedded,
  embeddedDataDeprecation,
  extractAllCustomTerms,
  hasEmbeddedData,
  previewFromEmbedded,
  stripEmbeddedData,
} from '../../../../src/scripts/editor/blocks/articles-homepage/item-preview';

const embedded = () => ({
  author: [{ id: 1, name: 'redaction' }],
  'wp:featuredmedia': [
    {
      id: 10,
      source_url: 'https://example.org/image.jpg',
      media_details: { image_meta: { caption: 'Légende longue' } },
    },
  ],
  'wp:term': [
    [
      {
        id: 2,
        name: 'Actualités',
        slug: 'actualites',
        taxonomy: 'category',
        acf: { category_singular_name: 'Actualité' },
      },
    ],
    [{ id: 3, name: 'Tag', slug: 'tag', taxonomy: 'post_tag' }],
    [{ id: 4, name: 'Afrique', slug: 'afrique', taxonomy: 'location', description: 'x' }],
  ],
  'acf:post': [{ id: 5, excerpt: { rendered: '<p>Article lié</p>' } }],
});

describe('extractAllCustomTerms', () => {
  it('keeps the terms of custom taxonomies only, reduced to their identity', () => {
    expect(extractAllCustomTerms(embedded())).toEqual([
      { id: 4, name: 'Afrique', slug: 'afrique', taxonomy: 'location' },
    ]);
  });

  it('returns no term without embedded terms', () => {
    expect(extractAllCustomTerms(null)).toEqual([]);
    expect(extractAllCustomTerms({})).toEqual([]);
  });
});

describe('chipFromEmbedded', () => {
  it('reads the singular name of the first term', () => {
    expect(chipFromEmbedded(embedded())).toEqual({ label: 'Actualité', slug: 'actualites' });
  });

  it('returns null when the first term has no singular name', () => {
    expect(chipFromEmbedded({ 'wp:term': [[{ slug: 'actualites' }]] })).toBeNull();
    expect(chipFromEmbedded(undefined)).toBeNull();
  });
});

describe('previewFromEmbedded', () => {
  it('keeps only the image URL and the chip', () => {
    expect(previewFromEmbedded(embedded())).toEqual({
      selectedPostImageUrl: 'https://example.org/image.jpg',
      selectedPostChip: { label: 'Actualité', slug: 'actualites' },
    });
  });

  it('falls back to empty values without embedded data', () => {
    expect(previewFromEmbedded(null)).toEqual({
      selectedPostImageUrl: '',
      selectedPostChip: null,
    });
  });
});

describe('hasEmbeddedData', () => {
  it('detects an item that still stores _embedded, even null', () => {
    expect(hasEmbeddedData([{ selectedPostId: 1 }, { _embedded: embedded() }])).toBe(true);
    expect(hasEmbeddedData([{ externalPost: true, _embedded: null }])).toBe(true);
  });

  it('ignores clean items and unexpected values', () => {
    expect(hasEmbeddedData([{ selectedPostId: 1, selectedPostImageUrl: '' }])).toBe(false);
    expect(hasEmbeddedData([null])).toBe(false);
    expect(hasEmbeddedData(undefined)).toBe(false);
  });
});

describe('stripEmbeddedData', () => {
  it('replaces _embedded with the preview fields and keeps the other fields', () => {
    const item = {
      category: 'actualites',
      selectedPostId: 1,
      selectedPostTitle: 'Titre',
      selectedPostSlug: 'titre',
      selectedPostCustomTerms: [{ id: 4, name: 'Afrique', slug: 'afrique', taxonomy: 'location' }],
      subtitle: 'Surtitre',
      externalPost: false,
      _embedded: embedded(),
    };

    const [stripped] = stripEmbeddedData([item]);

    expect(stripped).not.toHaveProperty('_embedded');
    expect(stripped).toEqual({
      category: 'actualites',
      selectedPostId: 1,
      selectedPostTitle: 'Titre',
      selectedPostSlug: 'titre',
      selectedPostCustomTerms: [{ id: 4, name: 'Afrique', slug: 'afrique', taxonomy: 'location' }],
      subtitle: 'Surtitre',
      externalPost: false,
      selectedPostImageUrl: 'https://example.org/image.jpg',
      selectedPostChip: { label: 'Actualité', slug: 'actualites' },
    });
  });

  it('returns clean items as they are', () => {
    const clean = { externalPost: true, externalItemImgUrl: 'https://example.org/ext.jpg' };

    expect(stripEmbeddedData([clean])[0]).toBe(clean);
  });
});

describe('embeddedDataDeprecation', () => {
  it('only migrates blocks whose items still store _embedded', () => {
    expect(embeddedDataDeprecation.isEligible({})).toBe(false);
    expect(embeddedDataDeprecation.isEligible({ items: [{ selectedPostId: 1 }] })).toBe(false);
    expect(embeddedDataDeprecation.isEligible({ items: [{ _embedded: null }] })).toBe(true);
  });

  it('keeps the other block attributes', () => {
    const migrated = embeddedDataDeprecation.migrate({
      className: 'is-style-x',
      lock: { move: true },
      items: [{ selectedPostId: 1, _embedded: embedded() }],
    });

    expect(migrated.className).toBe('is-style-x');
    expect(migrated.lock).toEqual({ move: true });
    expect(migrated.items[0]).not.toHaveProperty('_embedded');
    expect(migrated.items[0].selectedPostImageUrl).toBe('https://example.org/image.jpg');
  });
});
