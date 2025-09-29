import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/action', {
  title: __('Agir', 'amnesty'),
  description: __('Affiche un bloc de type "Pétition" ou "Action".', 'amnesty'),
  category: 'amnesty-core',
  icon: 'megaphone',
  attributes: {
    type: {
      type: 'string',
      default: 'petition',
    },
    surTitle: {
      type: 'string',
      default: '',
    },
    title: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    imageUrl: {
      type: 'string',
      default: '',
    },
    buttonText: {
      type: 'string',
      default: 'En savoir plus',
    },
    buttonLink: {
      type: 'string',
      default: '',
    },
    buttonPosition: {
      type: 'string',
      default: 'left',
    },
    backgroundColor: {
      type: 'string',
      default: 'primary',
    },
    petitionId: {
      type: 'number',
    },
    petitionData: {
      type: 'object',
    },
    overrideTitle: {
      type: 'string',
      default: '',
    },
    overrideImageUrl: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      type: 'petition',
      petitionId: 123,
      surTitle: 'ACTION URGENTE (Aperçu)',
      overrideTitle: '',
      overrideImageUrl: '',
      petitionData: {
        title: {
          rendered: 'Aperçu : Défendons la liberté d’expression',
        },
        featured_media_src_url: 'https://placehold.co/600x400/ffdb00/000000/png?text=Pétition',
        link: '#',
        acf: {
          objectif_signatures: 50000,
          date_de_fin: '2025-12-31',
        },
        meta: {
          _amnesty_signature_count: 23456,
        },
      },
    },
  },
  edit: EditComponent,
  save: () => null,
});
