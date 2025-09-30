import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/chapo', {
  title: __('Chapo', 'amnesty'),
  description: 'Block Chapo',
  category: 'amnesty-core',
  icon: 'editor-paragraph',
  attributes: {
    text: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      text: 'Dans un monde où les libertés fondamentales sont constamment menacées, chaque voix compte. Découvrez comment nos actions contribuent à défendre les droits humains pour toutes et tous.',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
