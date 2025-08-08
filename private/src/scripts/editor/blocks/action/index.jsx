import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/action', {
  title: __('Agir', 'amnesty'),
  description: __('Affiche un bloc de type "Pétition" ou "Action".', 'amnesty'),
  category: 'amnesty-core',
  icon: 'megaphone',
  attributes: {
    type: {
      type: 'string',
      default: 'petition',
    },
    surTitle: {
      type: 'string',
      default: '',
    },
    title: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    imageUrl: {
      type: 'string',
      default: '',
    },
    buttonText: {
      type: 'string',
      default: 'En savoir plus',
    },
    buttonLink: {
      type: 'string',
      default: '',
    },
    buttonPosition: {
      type: 'string',
      default: 'left',
    },
    backgroundColor: {
      type: 'string',
      default: 'primary',
    },
    petitionId: {
      type: 'number',
    },
    petitionData: {
      type: 'object',
    },
    overrideTitle: {
      type: 'string',
      default: '',
    },
    overrideImageUrl: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: () => null,
});
