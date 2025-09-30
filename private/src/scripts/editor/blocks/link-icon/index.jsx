import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/link-icon', {
  title: __('Lien avec icon', 'amnesty'),
  description: 'Block lien avec icon',
  category: 'amnesty-core',
  icon: 'admin-links',
  attributes: {
    title: {
      type: 'string',
      default: 'Titre',
    },
    titleSize: {
      type: 'string',
      default: 'medium',
    },
    description: {
      type: 'string',
      default: 'Description',
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
    displayButton: {
      type: 'boolean',
      default: true,
    },
  },
  example: {
    attributes: {
      title: 'Nos Thématiques',
      titleSize: 'medium',
      description: 'Explorez les grands enjeux sur lesquels nous nous mobilisons au quotidien.',
      icon: 'activism-abortions',
      bgColor: 'black',
      buttonLink: '#',
      displayButton: true,
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
