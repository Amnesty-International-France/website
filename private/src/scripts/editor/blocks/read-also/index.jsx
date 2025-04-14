import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/read-also', {
  title: __('Lire aussi', 'amnesty'),
  description: __('Block lire aussi', 'amnesty'),
  category: 'amnesty-core',
  attributes: {
    postId: {
      type: 'number',
    },
  },
  edit: EditComponent,
  save: () => null,
});
