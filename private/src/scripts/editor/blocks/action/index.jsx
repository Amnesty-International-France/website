import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/action', {
  title: __('Agir', 'amnesty'),
  description: 'Block Agir (Pétition/Action)',
  category: 'amnesty-core',
  attributes: {
    type: {
      type: 'string',
      default: 'petition',
    },
    title: {
      type: 'string',
      default: 'Titre',
    },
    subtitle: {
      type: 'string',
      default: 'Sous titre',
    },
    imageUrl: {
      type: 'string',
      default: '',
    },
    bgColor: {
      type: 'string',
      default: 'bg-white',
    },
    buttonLink: {
      type: 'string',
      default: '',
    },
    lignment: {
      type: 'string',
      default: 'left',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
