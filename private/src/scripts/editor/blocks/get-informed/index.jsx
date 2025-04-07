import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/get-informed', {
  title: __("S'informer", 'amnesty'),
  description: "Block s'informer",
  category: 'amnesty-core',
  attributes: {
    links: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
