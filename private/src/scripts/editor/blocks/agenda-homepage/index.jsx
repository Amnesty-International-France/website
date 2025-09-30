import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/agenda-homepage', {
  title: 'Agenda Homepage',
  description: "Section pour l'affichage d'évènements sur la page d'accueil",
  category: 'amnesty-core',
  icon: 'calendar-alt',
  attributes: {
    selectionMode: {
      type: 'string',
      default: 'latest',
    },
    selectedEventIds: {
      type: 'array',
      default: [],
    },
    firstEventId: {
      type: 'number',
      default: 0,
    },
    secondEventId: {
      type: 'number',
      default: 0,
    },
    chronicleImageUrl: {
      type: 'string',
      default: 0,
    },
    chronicleImageId: {
      type: 'number',
    },
  },
  example: {
    attributes: {
      selectionMode: 'latest',
      chronicleImageUrl: 'https://placehold.co/600x400/555/FFF/png?text=La+Chronique',
    },
  },
  edit: EditComponent,
  save: () => null,
});
