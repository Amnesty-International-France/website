import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty/content-callout', {
  title: __('Mise en exergue', 'amnesty'),
  category: 'amnesty-core',
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
  example: {
    attributes: {
      title: 'Notre constat',
      text: 'Plus de 75% des défenseurs des droits humains interrogés déclarent avoir subi des menaces directes en raison de leur travail.',
    },
  },
  edit: EditComponent,
  save: () => null,
});
