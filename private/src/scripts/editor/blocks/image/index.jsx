import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/image', {
  title: __('Image', 'amnesty'),
  description: 'Block Image',
  category: 'amnesty-core',
  attributes: {
    imageUrl: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    caption: {
      type: 'string',
      default: '',
    },
    altText: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
