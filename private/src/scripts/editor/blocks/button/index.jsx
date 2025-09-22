import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/button', {
  title: __('Bouton', 'amnesty'),
  description: 'Block Bouton',
  category: 'amnesty-core',
  attributes: {
    postId: {
      type: 'number',
      default: 0,
    },
    postType: {
      type: 'string',
      default: '',
    },
    internalUrl: {
      type: 'string',
      default: '',
    },
    internalUrlTitle: {
      type: 'string',
      default: '',
    },
    label: {
      type: 'string',
      default: 'Bouton',
    },
    size: {
      type: 'string',
      default: 'medium',
    },
    style: {
      type: 'string',
      default: 'bg-yellow',
    },
    icon: {
      type: 'string',
      default: '',
    },
    alignment: {
      type: 'string',
      default: 'left',
    },
    linkType: {
      type: 'string',
      default: 'internal',
    },
    externalUrl: {
      type: 'string',
      default: '',
    },
    targetBlank: {
      type: 'boolean',
      default: false,
    },
  },
  edit: EditComponent,
  save: () => null,
});
