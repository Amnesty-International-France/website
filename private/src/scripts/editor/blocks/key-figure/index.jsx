import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/key-figure', {
  title: __('Chiffre Clé', 'amnesty'),
  description: 'Block Chiffre Clé',
  category: 'amnesty-core',
  attributes: {
    title: {
      type: 'string',
      default: 'Titre',
    },
    text: {
      type: 'string',
      default: 'Texte',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
