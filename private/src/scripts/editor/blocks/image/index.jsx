import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/image', {
  title: 'Image + Légende',
  description: 'Block Image',
  category: 'amnesty-core',
  icon: 'format-image',
  attributes: {
    mediaId: {
      type: 'number',
    },
    className: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      mediaId: 1227,
    },
  },
  edit: EditComponent,
  save: () => null,
});
