import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/articles-homepage', {
  title: 'Articles Homepage',
  description: "Permet d'afficher une liste d'articles sur la page d'accueil.",
  category: 'amnesty-core',
  icon: 'layout',
  attributes: {
    items: {
      type: 'array',
      default: [
        { category: 'actualites', selectedPostId: null, subtitle: '' },
        { category: 'actualites', selectedPostId: null, subtitle: '' },
        { category: 'actualites', selectedPostId: null, subtitle: '' },
      ],
    },
  },
  example: {
    attributes: {
      items: [
        {
          category: 'actualites',
          selectedPostId: 101,
          selectedPostTitle: "Un titre d'article percutant pour l'aperçu",
          selectedPostSlug: 'titre-article-percutant-apercu',
          selectedPostDate: '2025-09-30T10:00:00',
          subtitle: "Ceci est un sous-titre pour mettre en avant l'article principal.",
          selectedPostCustomTerms: [
            { id: 1, name: 'Afrique', slug: 'afrique', taxonomy: 'region' },
          ],
          _embedded: {
            'wp:featuredmedia': [
              {
                source_url: 'https://placehold.co/800x600/222222/FFFFFF/png?text=Article+Principal',
              },
            ],
            'wp:term': [[{ id: 2, name: 'Actualités', slug: 'actualites', taxonomy: 'category' }]],
          },
        },
        {
          category: 'campagnes',
          selectedPostId: 102,
          selectedPostTitle: 'Notre nouvelle campagne pour le climat',
          selectedPostSlug: 'nouvelle-campagne-climat',
          selectedPostDate: '2025-09-28T14:00:00',
          selectedPostCustomTerms: [
            {
              id: 3,
              name: 'Justice climatique',
              slug: 'justice-climatique',
              taxonomy: 'thematique',
            },
          ],
          _embedded: {
            'wp:featuredmedia': [
              { source_url: 'https://placehold.co/600x400/ffdb00/000000/png?text=Campagne' },
            ],
            'wp:term': [[{ id: 4, name: 'Campagnes', slug: 'campagnes', taxonomy: 'category' }]],
          },
        },
        {
          category: 'landmark',
          selectedPostId: 103,
          selectedPostTitle: 'Repères : La situation au Yémen',
          selectedPostSlug: 'reperes-situation-yemen',
          selectedPostDate: '2025-09-25T11:00:00',
          selectedPostCustomTerms: [
            { id: 5, name: 'Crise humanitaire', slug: 'crise-humanitaire', taxonomy: 'thematique' },
          ],
          _embedded: {
            'wp:featuredmedia': [
              { source_url: 'https://placehold.co/600x400/555555/FFFFFF/png?text=Repères' },
            ],
            'wp:term': [[{ id: 6, name: 'Repères', slug: 'landmark', taxonomy: 'category' }]],
          },
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
