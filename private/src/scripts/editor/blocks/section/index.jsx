import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/section', {
  title: __('Section', 'amnesty'),
  description: 'Block section',
  category: 'amnesty-core',
  icon: 'layout',
  attributes: {
    sectionSize: {
      type: 'string',
      default: 'large',
    },
    title: {
      type: 'string',
      default: '',
    },
    showTitle: {
      type: 'boolean',
      default: true,
    },
    fullWidth: {
      type: 'boolean',
      default: true,
    },
    contentSize: {
      type: 'string',
      default: 'sm',
    },
    backgroundColor: {
      type: 'string',
      default: 'black',
    },
  },
  example: {
    attributes: {
      sectionSize: 'large',
      title: 'Notre Impact en Chiffres',
      showTitle: true,
      fullWidth: true,
      contentSize: 'md',
      backgroundColor: 'grey',
    },
    innerBlocks: [
      {
        name: 'core/paragraph',
        attributes: {
          content:
            'Chaque année, nos actions collectives entraînent des changements significatifs à travers le monde. Voici quelques exemples de ce que nous avons accompli ensemble.',
        },
      },
      {
        name: 'amnesty-core/key-figure',
        attributes: {
          title: '1.2 Million',
          text: "de signatures collectées pour la libération de prisonniers d'opinion.",
        },
      },
    ],
  },
  edit: EditComponent,
  save: SaveComponent,
});
