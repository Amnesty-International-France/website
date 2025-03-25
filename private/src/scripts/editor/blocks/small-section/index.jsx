import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/small-section', {
  title: __('Petite Section', 'amnesty'),
  description: 'Block petite section',
  category: 'amnesty-core',
  attributes: {
    title: {
      type: 'string',
      default: '',
    },
    showTitle: {
      type: 'boolean',
      default: true,
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
