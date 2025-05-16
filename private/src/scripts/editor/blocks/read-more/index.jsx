import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/read-more', {
  title: __('Lire la suite', 'amnesty'),
  description: 'Block Lire la suite',
  category: 'amnesty-core',
  attributes: {},
  edit: EditComponent,
  save: SaveComponent,
});
