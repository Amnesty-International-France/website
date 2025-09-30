import DisplayComponent from './DisplayComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/pop-in', {
  title: __('Pop-in', 'amnesty'),
  category: 'amnesty-core',
  icon: 'external',
  attributes: {},
  example: {
    innerBlocks: [
      {
        name: 'core/heading',
        attributes: {
          level: 3,
          content: 'Rejoignez le mouvement',
          textAlign: 'center',
        },
      },
      {
        name: 'core/paragraph',
        attributes: {
          content:
            'Chaque signature compte. Ajoutez la vôtre pour défendre les droits humains partout dans le monde.',
          align: 'center',
        },
      },
      {
        name: 'amnesty-core/button',
        attributes: {
          text: 'Je signe la pétition',
          url: '#',
          align: 'center',
        },
      },
    ],
  },
  edit: DisplayComponent,
  save: () => null,
});
