import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/clh-testimonials', {
  title: __('Ils vous disent merci (CLH)', 'amnesty'),
  description: __(
    'Témoignages de la page « Changez leur histoire », classés par année.',
    'amnesty',
  ),
  category: 'amnesty-core',
  icon: 'format-quote',
  attributes: {
    testimonials: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: () => null,
});
