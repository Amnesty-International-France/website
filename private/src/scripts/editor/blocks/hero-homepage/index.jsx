import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/hero-homepage', {
  title: 'Hero Homepage',
  description: 'Block for the hero homepage with 3 randomizable images.',
  category: 'amnesty-core',
  icon: 'cover-image',
  attributes: {
    items: {
      type: 'array',
      default: [
        { subtitle: '', mediaId: null, mediaMobileId: null, buttonLabel: '', buttonUrl: '' },
        { subtitle: '', mediaId: null, mediaMobileId: null, buttonLabel: '', buttonUrl: '' },
        { subtitle: '', mediaId: null, mediaMobileId: null, buttonLabel: '', buttonUrl: '' },
      ],
    },
    className: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      items: [
        {
          subtitle: 'Votre voix est une arme puissante. Utilisez-la.',
          mediaId: 1220,
          buttonLabel: 'Découvrir nos campagnes',
          buttonUrl: '#',
        },
        { subtitle: 'Slide 2', mediaId: 1221, mediaMobileId: 1221, buttonLabel: '', buttonUrl: '' },
        { subtitle: 'Slide 3', mediaId: 1222, mediaMobileId: 1222, buttonLabel: '', buttonUrl: '' },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
