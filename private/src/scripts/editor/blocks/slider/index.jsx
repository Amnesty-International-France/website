import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/slider', {
  title: __('Slider', 'amnesty'),
  description: __("Affiche un sliderd'articles ou d'un autre type de contenu.", 'amnesty'),
  category: 'amnesty-core',
  attributes: {
    postType: {
      type: 'string',
      default: '',
    },
    selectedPosts: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: () => null,
});
