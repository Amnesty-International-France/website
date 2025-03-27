import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/blockquote', {
  title: __('Citation', 'amnesty'),
  description: 'Block citation',
  category: 'amnesty-core',
  attributes: {
    quoteText: {
      type: 'string',
      default: 'Saissisez votre citation',
    },
    author: {
      type: 'string',
      default: "Indiquez l'auteur",
    },
    showImage: {
      type: 'boolean',
      default: false,
    },
    imageUrl: {
      type: 'string',
      default: '',
    },
    bgColor: {
      type: 'string',
      default: 'black',
    },
    size: {
      type: 'string',
      default: 'medium',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
