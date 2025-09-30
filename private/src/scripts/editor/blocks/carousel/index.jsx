import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/carousel', {
  title: __('Carousel', 'amnesty'),
  description: "Block carrousel d'images",
  category: 'amnesty-core',
  icon: 'slides',
  attributes: {
    mediaIds: {
      type: 'array',
      default: [],
    },
  },
  example: {
    attributes: {
      mediaIds: [606, 100, 250, 322, 388, 425],
    },
  },
  edit: EditComponent,
  save: () => null,
});
