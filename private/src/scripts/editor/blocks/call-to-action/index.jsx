import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/call-to-action', {
  title: __('Call to Action', 'amnesty'),
  description: 'Block CTA',
  category: 'amnesty-core',
  attributes: {
    direction: {
      type: 'string',
      default: 'horizontal',
    },
    title: {
      type: 'string',
      default: 'Titre',
    },
    subTitle: {
      type: 'string',
      default: 'Sous-titre',
    },
    buttonLabel: {
      type: 'string',
      default: 'Label',
    },
    buttonLink: {
      type: 'string',
      default: '#',
    },
    customId: {
      type: 'string',
      default: '',
    },
    geolocation: {
      type: 'boolean',
      default: false,
    },
  },
  edit: EditComponent,
  save: () => null,
});
