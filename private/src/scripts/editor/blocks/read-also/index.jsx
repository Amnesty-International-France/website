import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/read-also', {
  title: __('Lire aussi', 'amnesty'),
  description: 'Block lire aussi',
  category: 'amnesty-core',
  attributes: {
    text: {
      type: 'string',
      default: '',
    },
    link: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
