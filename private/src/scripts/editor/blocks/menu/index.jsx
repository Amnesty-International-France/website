import DisplayComponent from './DisplayComponent.jsx';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('amnesty-core/block-menu', {
  // translators: [admin]
  title: __('Menu', 'amnesty'),
  icon: 'welcome-widgets-menus',
  category: 'amnesty-core',
  keywords: [
    // translators: [admin]
    __('Menu', 'amnesty'),
    // translators: [admin]
    __('Navigation', 'amnesty'),
  ],
  attributes: {
    menuId: {
      type: 'integer',
    },
    color: {
      type: 'string',
    },
    type: {
      type: 'string',
      default: 'standard-menu',
    },
  },
  example: {
    attributes: {
      type: 'standard-menu',
      menuId: 1,
      color: '',
    },
  },
  edit: DisplayComponent,
  save: () => null,
});
