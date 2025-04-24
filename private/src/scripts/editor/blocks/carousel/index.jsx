import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/carousel', {
  title: __('Carousel', 'amnesty'),
  description: "Block carousel d'images",
  category: 'amnesty-core',
  attributes: {
    mediaIds: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: () => null,
});
