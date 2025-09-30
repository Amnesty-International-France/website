import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/video', {
  title: __('Video', 'amnesty'),
  description: 'Block Video',
  category: 'amnesty-core',
  icon: 'video-alt3',
  attributes: {
    url: {
      type: 'string',
    },
    title: {
      type: 'string',
      default: '',
    },
  },
  example: {
    attributes: {
      url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      title: 'Notre combat pour les droits humains en vidéo',
    },
  },
  edit: EditComponent,
  save: () => null,
});
