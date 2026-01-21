import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/slider-changez-leur-histoire', {
  title: __('Slider Changez leur histoire', 'amnesty'),
  description: __('Affiche un slider de cartes de pétitions.', 'amnesty'),
  category: 'amnesty-core',
  icon: 'slides',
  attributes: {
    petitionType: {
      type: 'string',
      default: '',
    },
    selectedPosts: {
      type: 'array',
      default: [],
    },
  },
  example: {
    attributes: {
      petitionType: 'petition',
      selectedPosts: [
        {
          id: 1,
          title: 'Pétition en vedette',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/222/FFF/png?text=Petition+1',
        },
        {
          id: 2,
          title: 'Agir maintenant',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/555/FFF/png?text=Petition+2',
        },
        {
          id: 3,
          title: 'Action urgente',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/888/FFF/png?text=Petition+3',
        },
        {
          id: 4,
          title: 'Mobilisation citoyenne',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/aaa/000/png?text=Petition+4',
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
