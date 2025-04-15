import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/section', {
  title: __('Section', 'amnesty'),
  description: 'Block section',
  category: 'amnesty-core',
  attributes: {
    sectionSize: {
      type: 'string',
      default: 'large',
    },
    title: {
      type: 'string',
      default: '',
    },
    showTitle: {
      type: 'boolean',
      default: true,
    },
    fullWidth: {
      type: 'boolean',
      default: true,
    },
    contentSize: {
      type: 'string',
      default: 'sm',
    },
    backgroundColor: {
      type: 'string',
      default: 'black',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
