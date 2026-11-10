import { TESTIMONIALS_BLOCK_NAME } from '../../clh/testimonial-validation';
import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType(TESTIMONIALS_BLOCK_NAME, {
  title: __('Ils vous disent merci (CLH)', 'amnesty'),
  description: __(
    'Témoignages de la page « Changez leur histoire », classés par année.',
    'amnesty',
  ),
  category: 'amnesty-core',
  icon: 'format-quote',
  attributes: {
    title: { type: 'string', default: __('Ils vous disent merci', 'amnesty') },
    subtitle: { type: 'string', default: '' },
    testimonials: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: () => null,
});
