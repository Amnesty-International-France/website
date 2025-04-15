import EditComponent from './EditComponent.jsx';

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
    fileIds: {
      type: 'array',
      default: [],
    },
  },
  edit: EditComponent,
  save: () => null,
});
