import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/download-go-further', {
  title: __('Pour aller plus loin', 'amnesty'),
  description: 'Block pour aller plus loin, téléchargement de fichiers',
  category: 'amnesty-core',
  attributes: {
    title: {
      type: 'string',
      default: 'Titre',
    },
  },
  edit: EditComponent,
  save: SaveComponent,
});
