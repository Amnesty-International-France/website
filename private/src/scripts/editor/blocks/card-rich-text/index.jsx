import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/card-rich-text', {
  title: __('Carte image / texte enrichie', 'amnesty'),
  description: __('Carte avec image et texte riche intégral', 'amnesty'),
  category: 'amnesty-core',
  icon: 'id-alt',
  attributes: {
    thumbnail: {
      type: 'integer',
      default: null,
    },
    surtitle: {
      type: 'string',
      default: '',
    },
    title: {
      type: 'string',
      default: '',
    },
    permalink: {
      type: 'string',
      default: '',
    },
    linkTitle: {
      type: 'string',
      default: 'Voir la suite',
    },
    newTab: {
      type: 'boolean',
      default: false,
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
