import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/actions-homepage', {
  title: 'Actions Homepage',
  description: "Section pour l'affichage d'actions sur la page d'accueil",
  category: 'amnesty-core',
  attributes: {
    title: {
      type: 'string',
      default: "S'engager",
    },
    chapo: {
      type: 'string',
      default: '',
    },
    items: {
      type: 'array',
      default: [
        {
          itemTitle: 'Titre du bloc 1',
          itemDescription: 'Description du bloc 1',
          buttonLabel: 'Label du bouton',
          buttonLink: '#',
          imageUrl: '',
        },
        {
          itemTitle: 'Titre du bloc 2',
          itemDescription: 'Description du bloc 2',
          buttonLabel: 'Label du bouton',
          buttonLink: '',
          imageUrl: '',
        },
        {
          itemTitle: 'Titre du bloc 3',
          itemDescription: 'Description du bloc 3',
          buttonLabel: 'Label du bouton',
          buttonLink: '',
          imageUrl: '',
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
