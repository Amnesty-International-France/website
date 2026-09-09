const { registerBlockStyle } = wp.blocks;
const { _x } = wp.i18n;

registerBlockStyle('amnesty-core/image', {
  name: 'captioned',
  isDefault: true,
  // translators: [admin]
  label: _x('Image avec légende', 'block style', 'amnesty'),
});

registerBlockStyle('amnesty-core/image', {
  name: 'simple',
  // translators: [admin]
  label: _x('Image simple', 'block style', 'amnesty'),
});
