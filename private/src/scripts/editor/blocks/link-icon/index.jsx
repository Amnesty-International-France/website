import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/link-icon', {
  title: __('Lien avec icon', 'amnesty'),
  description: 'Block lien avec icon',
  category: 'amnesty-core',
  attributes: {
    title: {
      type: 'string',
      default: 'Titre du lien',
    },
    titleSize: {
      type: 'string',
      default: 'medium',
    },
    description: {
      type: 'string',
      default: 'Description du lien',
    },
    icon: {
      type: 'string',
      default: 'activism-abortions',
    },
    bgColor: {
      type: 'string',
      default: 'black',
    },
    buttonLink: {
      type: 'string',
      default: '#',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
