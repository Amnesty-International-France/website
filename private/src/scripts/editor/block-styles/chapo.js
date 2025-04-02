const { registerBlockStyle } = wp.blocks;
const { _x } = wp.i18n;

registerBlockStyle('core/paragraph', {
  name: 'chapo',
  // translators: [admin]
  label: _x('Chapo', 'block style', 'amnesty'),
});
