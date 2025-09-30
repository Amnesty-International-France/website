import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/section-home', {
  title: 'Home de rubrique',
  description: __('Section home de rubrique', 'amnesty'),
  category: 'amnesty-core',
  icon: 'layout',
  attributes: {
    title: {
      type: 'string',
      default: 'Titre',
    },
    text: {
      type: 'string',
      default: '',
    },
    bgColor: {
      type: 'string',
      default: 'black',
    },
    showImage: {
      type: 'boolean',
      default: false,
    },
    mediaId: {
      type: 'number',
    },
    mediaUrl: {
      type: 'string',
      default: '',
    },
    mediaPosition: {
      type: 'string',
      default: 'left',
    },
    mediaCaption: {
      type: 'string',
      default: '',
    },
    mediaDescription: {
      type: 'string',
      default: '',
    },
    icons: {
      type: 'array',
      default: [],
      items: {
        type: 'object',
        properties: {
          icon: {
            type: 'string',
            default: '',
          },
          text: {
            type: 'string',
            default: '',
          },
          link: {
            type: 'string',
            default: '',
          },
          linkTitle: {
            type: 'string',
            default: '',
          },
        },
      },
    },
    displayButton: {
      type: 'boolean',
      default: false,
    },
    buttonLabel: {
      type: 'string',
      default: 'Label du bouton',
    },
    buttonLink: {
      type: 'string',
      default: '#',
    },
    buttonPosition: {
      type: 'string',
      default: 'center',
    },
    buttonContentType: {
      type: 'string',
      default: '',
    },
    buttonLinkTitle: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      title: 'Nos combats, votre pouvoir',
      text: 'Chaque jour, nous luttons pour un monde où les droits humains sont respectés par tous. Découvrez les thématiques au cœur de nos actions.',
      bgColor: 'grey',
      showImage: true,
      mediaUrl: 'https://placehold.co/600x800/222/FFF/png?text=Image+Section',
      mediaPosition: 'left',
      mediaCaption: 'Crédit photo',
      mediaDescription: 'Description photo',
      icons: [
        {
          icon: 'activism-death-penalty',
          text: 'Peine de mort',
          link: '#',
          linkTitle: 'Peine de mort',
        },
        {
          icon: 'activism-freedom-of-expression',
          text: "Liberté d'expression",
          link: '#',
          linkTitle: "Liberté d'expression",
        },
        {
          icon: 'activism-armed-conflict',
          text: 'Conflits armés',
          link: '#',
          linkTitle: 'Conflits armés',
        },
      ],
      displayButton: true,
      buttonLabel: 'Toutes nos thématiques',
      buttonLink: '#',
      buttonPosition: 'left',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
