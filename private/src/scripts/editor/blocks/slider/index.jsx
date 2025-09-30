import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/slider', {
  title: __('Slider', 'amnesty'),
  description: __("Affiche un slider d'articles ou d'un autre type de contenu.", 'amnesty'),
  category: 'amnesty-core',
  icon: 'slides',
  attributes: {
    postType: {
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
      postType: 'actualites',
      selectedPosts: [
        {
          id: 1,
          title: 'Article en vedette N°1',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/222/FFF/png?text=Slide+1',
        },
        {
          id: 2,
          title: 'Une autre actualité importante',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/555/FFF/png?text=Slide+2',
        },
        {
          id: 3,
          title: 'Le résumé de notre dernière action',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/888/FFF/png?text=Slide+3',
        },
        {
          id: 4,
          title: 'Analyse et perspectives futures',
          link: '#',
          featured_media_url: 'https://placehold.co/600x400/aaa/000/png?text=Slide+4',
        },
      ],
    },
  },
  edit: EditComponent,
  save: () => null,
});
