import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/au-thematic-register-form', {
  title: __('AU Thématique', 'amnesty'),
  description: __("Affiche un formulaire d'inscription Action Urgente thématique", 'amnesty'),
  category: 'amnesty-core',
  icon: 'megaphone',
  attributes: {
    title: {
      type: 'string',
      default: '',
    },
    textHeader: {
      type: 'string',
      default: '',
    },
    thematique: {
      type: 'string',
    },
  },
  example: {
    attributes: {
      title: 'ACTION URGENTE Justice Climatique',
      textHeader:
        "Inscrivez-vous pour recevoir les emails d'action urgente concernant la justice climatique.",
      thematique: 'Justice climatique',
    },
  },
  edit: EditComponent,
  save: () => null,
});
