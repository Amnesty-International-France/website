import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/video', {
  title: __('Video', 'amnesty'),
  description: 'Block Video',
  category: 'amnesty-core',
  attributes: {
    url: {
      type: 'string',
    },
    title: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: () => null,
});
