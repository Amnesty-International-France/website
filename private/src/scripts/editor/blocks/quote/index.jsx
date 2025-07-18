import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/blockquote', {
  title: __('Citation', 'amnesty'),
  description: 'Block citation',
  category: 'amnesty-core',
  attributes: {
    quoteText: {
      type: 'string',
      default: 'Saisissez votre citation',
    },
    author: {
      type: 'string',
      default: "Indiquez l'auteur",
    },
    showImage: {
      type: 'boolean',
      default: false,
    },
    imageId: {
      type: 'number',
      default: null,
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
  save: () => null,
});
