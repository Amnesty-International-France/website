import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/articles-homepage', {
  title: 'Articles Homepage',
  description: "Permet d'afficher une liste d'articles sur la page d'accueil.",
  category: 'amnesty-core',
  attributes: {
    items: {
      type: 'array',
      default: [
        { category: 'actualites', selectedPostId: null, subtitle: '' },
        { category: 'actualites', selectedPostId: null, subtitle: '' },
        { category: 'actualites', selectedPostId: null, subtitle: '' },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
