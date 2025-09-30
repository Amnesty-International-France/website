import EditComponent from './EditComponent.jsx';

const { registerBlockType } = wp.blocks;

registerBlockType('amnesty-core/mission-homepage', {
  title: 'Mission Homepage',
  description: "Permet d'afficher le bloc Notre Mission sur la page d'accueil.",
  category: 'amnesty-core',
  icon: 'flag',
  example: {},
  edit: EditComponent,
  save: () => null,
});
