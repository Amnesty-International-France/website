import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/get-informed', {
  title: __("S'informer", 'amnesty'),
  description: "Block s'informer",
  category: 'amnesty-core',
  icon: 'info-outline',
  attributes: {
    links: {
      type: 'array',
      default: [],
    },
  },
  example: {
    attributes: {
      links: [
        {
          type: 'dossier',
          title: 'Comprendre la crise des droits humains au Darfour',
          url: '#',
          customLabel: '',
        },
        {
          type: 'pays',
          title: 'Situation actuelle en Iran : un combat pour la liberté',
          url: '#',
          customLabel: '',
        },
        {
          type: 'libre',
          title: 'Notre rapport annuel est disponible en téléchargement',
          url: '#',
          customLabel: 'TÉLÉCHARGER LE RAPPORT',
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
