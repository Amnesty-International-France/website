import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/call-to-action', {
  title: __('Call to Action', 'amnesty'),
  description: 'Block CTA',
  category: 'amnesty-core',
  icon: 'star-filled',
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
    linkType: {
      type: 'string',
      default: 'external',
    },
    internalUrl: {
      type: 'string',
      default: '',
    },
    externalUrl: {
      type: 'string',
      default: '#',
    },
    postId: {
      type: 'number',
    },
    internalUrlTitle: {
      type: 'string',
      default: '',
    },
    targetBlank: {
      type: 'boolean',
      default: false,
    },
  },
  example: {
    attributes: {
      direction: 'horizontal',
      title: 'Prêt·e à faire la différence ?',
      subTitle: 'Chaque action, même la plus petite, contribue à un monde plus juste.',
      buttonLabel: 'Découvrir nos actions',
      linkType: 'external',
      externalUrl: '#',
    },
  },
  edit: EditComponent,
  save: () => null,
});
