import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/button', {
  title: __('Bouton', 'amnesty'),
  description: 'Block Bouton',
  category: 'amnesty-core',
  attributes: {
    label: {
      type: 'string',
      default: 'Bouton',
    },
    size: {
      type: 'string',
      default: 'medium',
    },
    style: {
      type: 'string',
      default: 'bg-yellow',
    },
    icon: {
      type: 'string',
      default: '',
    },
    link: {
      type: 'string',
      default: '',
    },
    alignment: {
      type: 'string',
      default: 'left',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
