import EditComponent from './EditComponent.jsx';
import { embeddedDataDeprecation } from './item-preview';

const { registerBlockType } = wp.blocks;

const blockAttributes = {
  items: {
    type: 'array',
    default: [
      {
        category: 'actualites',
        selectedPostId: null,
        subtitle: '',
        externalPost: false,
        externalItemTitle: '',
        externalItemLink: '',
        externalItemImgId: null,
        externalItemImgUrl: '',
      },
      {
        category: 'actualites',
        selectedPostId: null,
        subtitle: '',
        externalPost: false,
        externalItemLink: '',
        externalItemTitle: '',
        externalItemImgId: null,
        externalItemImgUrl: '',
      },
      {
        category: 'actualites',
        selectedPostId: null,
        subtitle: '',
        externalPost: false,
        externalItemLink: '',
        externalItemTitle: '',
        externalItemImgId: null,
        externalItemImgUrl: '',
      },
    ],
  },
};

registerBlockType('amnesty-core/articles-homepage', {
  title: 'Articles Homepage',
  description: "Permet d'afficher une liste d'articles sur la page d'accueil.",
  category: 'amnesty-core',
  icon: 'layout',
  attributes: blockAttributes,
  example: {
    attributes: {
      items: [
        {
          category: 'actualites',
          selectedPostId: 101,
          selectedPostTitle: "Un titre d'article percutant pour l'aperçu",
          selectedPostSlug: 'titre-article-percutant-apercu',
          subtitle: "Ceci est un sous-titre pour mettre en avant l'article principal.",
          selectedPostCustomTerms: [
            { id: 1, name: 'Afrique', slug: 'afrique', taxonomy: 'region' },
          ],
          selectedPostImageUrl:
            'https://placehold.co/800x600/222222/FFFFFF/png?text=Article+Principal',
          selectedPostChip: { label: 'Actualité', slug: 'actualites' },
        },
        {
          category: 'campagnes',
          selectedPostId: 102,
          selectedPostTitle: 'Notre nouvelle campagne pour le climat',
          selectedPostSlug: 'nouvelle-campagne-climat',
          selectedPostCustomTerms: [
            {
              id: 3,
              name: 'Justice climatique',
              slug: 'justice-climatique',
              taxonomy: 'thematique',
            },
          ],
          selectedPostImageUrl: 'https://placehold.co/600x400/ffdb00/000000/png?text=Campagne',
          selectedPostChip: { label: 'Campagne', slug: 'campagnes' },
        },
        {
          category: 'landmark',
          selectedPostId: 103,
          selectedPostTitle: 'Repères : La situation au Yémen',
          selectedPostSlug: 'reperes-situation-yemen',
          selectedPostCustomTerms: [
            { id: 5, name: 'Crise humanitaire', slug: 'crise-humanitaire', taxonomy: 'thematique' },
          ],
          selectedPostImageUrl: 'https://placehold.co/600x400/555555/FFFFFF/png?text=Repères',
          selectedPostChip: { label: 'Repère', slug: 'reperes' },
        },
      ],
    },
  },
  deprecated: [
    {
      // Items used to store the whole REST `_embedded` payload of the selected post (MAINT-307).
      attributes: blockAttributes,
      ...embeddedDataDeprecation,
      save: () => null,
    },
  ],
  edit: EditComponent,
  save: () => null,
});
