const wordpress = require('@wordpress/eslint-plugin');
const prettier = require('eslint-config-prettier');
const react = require('eslint-plugin-react');
const reactHooks = require('eslint-plugin-react-hooks');
const globals = require('globals');
const airbnb = require('./eslint.airbnb.js');

module.exports = [
  {
    ignores: ['tests/e2e/**/*.mjs'],
  },
  airbnb,
  prettier,
  react.configs.flat.recommended,
  {
    plugins: { 'react-hooks': reactHooks },
    rules: {
      'react-hooks/rules-of-hooks': 'error',
      'react-hooks/exhaustive-deps': 'warn',
    },
  },
  {
    plugins: { '@wordpress': wordpress },
    rules: wordpress.configs.i18n.rules,
  },
  {
    files: ['**/*.{js,jsx,mjs,cjs}'],
    languageOptions: {
      ecmaVersion: 'latest',
      globals: {
        ...globals.browser,
        lodash: 'writable',
        React: 'writable',
        ReactDOM: 'writable',
        wp: 'writable',
      },
    },
    settings: {
      react: {
        version: '18.3.1',
      },
    },
    rules: {
      'react/prop-types': 'off',
      'import-x/no-extraneous-dependencies': [
        'error',
        {
          devDependencies: true,
        },
      ],
      '@wordpress/i18n-text-domain': [
        'error',
        {
          allowedTextDomain: ['amnesty', 'default'],
        },
      ],
      'no-underscore-dangle': [
        'error',
        {
          allow: ['_embedded', '_EventStartDate', '_EventEndDate', '_VenueCity', '_OrganizerEmail'],
        },
      ],
      'prefer-destructuring': 'off',
    },
  },
];
