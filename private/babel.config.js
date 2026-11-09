const presets = [
  [
    '@babel/preset-env',
    {
      modules: 'auto',
      shippedProposals: true,
    },
  ],
  [
    '@babel/preset-react',
    {
      runtime: 'classic',
      pragma: 'wp.element.createElement',
      pragmaFrag: 'wp.element.Fragment',
      throwIfNamespace: false,
    },
  ],
];

const plugins = [
  ['@babel/plugin-transform-optional-chaining'],
  ['@babel/plugin-transform-class-properties'],
  ['babel-plugin-polyfill-corejs3', { method: 'usage-global', version: '3.40' }],
];

module.exports = {
  presets,
  plugins,
};
