import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/section-home', {
  title: 'Home de rubrique',
  description: __('Section home de rubrique', 'amnesty'),
  category: 'amnesty-core',
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
  edit: EditComponent,
  save: SaveComponent,
});
