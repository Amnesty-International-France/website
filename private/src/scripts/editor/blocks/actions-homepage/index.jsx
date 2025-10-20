import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/actions-homepage', {
  title: 'Actions Homepage',
  description: "Section pour l'affichage d'actions sur la page d'accueil",
  category: 'amnesty-core',
  icon: 'grid-view',
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
          imageUrl: '',
          linkType: 'external',
          externalUrl: '#',
          internalUrl: '',
          internalUrlTitle: '',
          postId: 0,
          postType: '',
          targetBlank: false,
        },
        {
          itemTitle: 'Titre du bloc 2',
          itemDescription: 'Description du bloc 2',
          buttonLabel: 'Label du bouton',
          imageUrl: '',
          linkType: 'external',
          externalUrl: '',
          internalUrl: '',
          internalUrlTitle: '',
          postId: 0,
          postType: '',
          targetBlank: false,
        },
        {
          itemTitle: 'Titre du bloc 3',
          itemDescription: 'Description du bloc 3',
          buttonLabel: 'Label du bouton',
          imageUrl: '',
          linkType: 'external',
          externalUrl: '',
          internalUrl: '',
          internalUrlTitle: '',
          postId: 0,
          postType: '',
          targetBlank: false,
        },
      ],
    },
  },
  example: {
    attributes: {
      title: 'Nos combats actuels',
      chapo:
        'De la défense des droits humains à la lutte contre la discrimination, découvrez comment vous pouvez faire la différence dès aujourd’hui.',
      items: [
        {
          itemTitle: "Liberté d'expression",
          itemDescription:
            'Partout dans le monde, des voix sont réduites au silence. Aidez-nous à protéger les journalistes et les activistes.',
          buttonLabel: 'Défendre ce droit',
          imageUrl: 'https://placehold.co/600x400/222222/FFFFFF/png?text=Liberté',
          linkType: 'external',
          externalUrl: '#',
          targetBlank: false,
        },
        {
          itemTitle: 'Contre la torture',
          itemDescription:
            'La torture est une pratique barbare qui doit cesser. Signez nos pétitions pour exiger la justice pour les victimes.',
          buttonLabel: 'Exiger la justice',
          imageUrl: 'https://placehold.co/600x400/ffdb00/000000/png?text=Justice',
          linkType: 'external',
          externalUrl: '#',
          targetBlank: false,
        },
        {
          itemTitle: 'Droits des réfugiés',
          itemDescription:
            'Des millions de personnes sont forcées de fuir leur foyer. Agissez pour garantir leur sécurité et leur dignité.',
          buttonLabel: 'Agir pour eux',
          imageUrl: 'https://placehold.co/600x400/555555/FFFFFF/png?text=Solidarité',
          linkType: 'external',
          externalUrl: '#',
          targetBlank: false,
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
