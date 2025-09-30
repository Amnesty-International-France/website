import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/card-image-text', {
  title: 'Carte image / texte',
  description: 'Block carte avec image + texte',
  category: 'amnesty-core',
  icon: 'id-alt',
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
  example: {
    attributes: {
      custom: false,
      direction: 'vertical',
      title: "Le Droit à l'Information : Un Pilier de la Démocratie",
      subtitle: "Notre dernier rapport d'enquête",
      category: 'Actualités',
      permalink: '#',
      text: "<p>Découvrez notre analyse approfondie sur les défis que rencontrent les journalistes et les défenseurs des droits humains pour accéder à l'information et la diffuser librement.</p>",
      thumbnail: '4',
    },
  },
  edit: EditComponent,
  save: () => null,
});
