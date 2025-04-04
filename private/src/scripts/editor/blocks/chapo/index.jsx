import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/chapo', {
  title: __('Chapo', 'amnesty'),
  description: 'Block Chapo',
  category: 'amnesty-core',
  attributes: {
    text: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
