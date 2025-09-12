import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty/rubric-heading', {
  apiVersion: 2,
  title: __('Titre de Rubrique', 'amnesty'),
  category: 'amnesty-blocks',
  icon: 'heading',
  description: __(
    'Un titre de rubrique pour le sommaire, avec une catégorie, un titre principal et un soulignement.',
    'amnesty',
  ),
  attributes: {
    kicker: {
      type: 'string',
      source: 'html',
      selector: '.rubric-heading__kicker',
      default: 'Catégorie',
    },
    heading: {
      type: 'string',
      source: 'html',
      selector: '.rubric-heading__heading',
      default: 'Titre de la rubrique',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
