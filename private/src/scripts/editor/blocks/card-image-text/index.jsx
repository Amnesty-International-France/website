import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/card-image-text', {
  title: 'Carte image / texte',
  description: 'Block carte avec image + texte',
  category: 'amnesty-core',
  attributes: {
    custom: {
      type: 'boolean',
      default: false,
    },
    direction: {
      type: 'string',
      default: 'vertical',
    },
    postId: {
      type: 'integer',
      default: null,
    },
    title: {
      type: 'string',
      default: 'Titre par défaut',
    },
    subtitle: {
      type: 'string',
      default: 'Sous-titre par défaut',
    },
    category: {
      type: 'string',
      default: 'Categorie',
    },
    permalink: {
      type: 'string',
      default: '#',
    },
    thumbnail: {
      type: 'integer',
      default: null,
    },
    text: {
      type: 'string',
      default: 'Texte par défaut',
    },
    selectedPostCategorySlug: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: () => null,
});
