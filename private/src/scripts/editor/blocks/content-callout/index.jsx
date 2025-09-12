import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty/content-callout', {
  apiVersion: 2,
  title: __('Mise en exergue', 'amnesty'),
  category: 'amnesty-blocks',
  icon: 'megaphone',
  description: __(
    "Un bloc pour attirer l'attention sur une information clé, avec un titre et un texte.",
    'amnesty',
  ),
  attributes: {
    title: {
      type: 'string',
      default: 'Lorem ipsum dolor sit amet',
    },
    text: {
      type: 'string',
      default:
        'Consectetur adipiscing elit. Curabitur nec neque erat. Vestibulum molestie sem augue, ac congue nulla faucibus id. Sed placerat scelerisque tristique.',
    },
  },
  edit: EditComponent,
  save: () => null,
});
