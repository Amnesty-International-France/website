import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/read-also', {
  title: __('Lire aussi', 'amnesty'),
  description: __('Block lire aussi', 'amnesty'),
  category: 'amnesty-core',
  attributes: {
    linkType: {
      type: 'string',
      default: 'internal',
    },
    externalUrl: {
      type: 'string',
      default: '',
    },
    externalLabel: {
      type: 'string',
      default: '',
    },
    postId: {
      type: 'number',
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
    targetBlank: {
      type: 'boolean',
      default: false,
    },
  },
  edit: EditComponent,
  save: () => null,
});
