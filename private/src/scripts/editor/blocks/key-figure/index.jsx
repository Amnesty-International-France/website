import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/key-figure', {
  title: __('Chiffre Clé', 'amnesty'),
  description: 'Block Chiffre Clé',
  category: 'amnesty-core',
  icon: 'analytics',
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
  example: {
    attributes: {
      title: '84 millions',
      text: "C'est le nombre de personnes déracinées de force dans le monde à la fin de 2021.",
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
