import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/image', {
  title: 'Image + Légende',
  description: 'Block Image',
  category: 'amnesty-core',
  attributes: {
    mediaId: {
      type: 'number',
    },
    className: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: () => null,
});
