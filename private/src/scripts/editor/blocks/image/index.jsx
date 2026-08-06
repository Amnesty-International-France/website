import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/image', {
  title: 'Image',
  description: 'Bloc Image',
  category: 'amnesty-core',
  icon: 'format-image',
  attributes: {
    mediaId: {
      type: 'number',
    },
    mediaMobileId: {
      type: 'number',
    },
    className: {
      type: 'string',
      default: '',
    },
    fullWidth: {
      type: 'boolean',
      default: false,
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
