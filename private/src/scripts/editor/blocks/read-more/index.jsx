import EditComponent from './EditComponent.jsx';
import SaveComponent from './SaveComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/read-more', {
  title: __('Lire la suite', 'amnesty'),
  description: 'Block Lire la suite',
  category: 'amnesty-core',
  icon: 'editor-insertmore',
  attributes: {},
  example: {
    attributes: {},
    innerBlocks: [
      {
        name: 'amnesty-core/read-also',
        attributes: {
          linkType: 'external',
          externalUrl: '#',
          externalLabel: 'Notre rapport complet sur la situation des droits humains en 2025',
          targetBlank: true,
        },
      },
    ],
  },
  edit: EditComponent,
  save: SaveComponent,
});
