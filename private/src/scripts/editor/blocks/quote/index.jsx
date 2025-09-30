import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/blockquote', {
  title: __('Citation', 'amnesty'),
  description: 'Block citation',
  category: 'amnesty-core',
  icon: 'format-quote',
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
  example: {
    attributes: {
      quoteText:
        "Le silence face à l'injustice fait de nous des complices. Notre mission est de faire entendre la voix de ceux qu'on tente de faire taire.",
      author: 'Amnesty International',
      showImage: true,
      imageId: 1022,
      bgColor: 'black',
      size: 'medium',
    },
  },
  edit: EditComponent,
  save: () => null,
});
