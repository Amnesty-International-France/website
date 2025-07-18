import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/hero-homepage', {
  title: 'Hero Homepage',
  description: 'Block for the hero homepage with 3 randomizable images.',
  category: 'amnesty-core',
  attributes: {
    items: {
      type: 'array',
      default: [
        { subtitle: '', mediaId: null, buttonLabel: '', buttonUrl: '' },
        { subtitle: '', mediaId: null, buttonLabel: '', buttonUrl: '' },
        { subtitle: '', mediaId: null, buttonLabel: '', buttonUrl: '' },
      ],
    },
    className: {
      type: 'string',
      default: '',
    },
  },
  edit: EditComponent,
  save: () => null,
});
