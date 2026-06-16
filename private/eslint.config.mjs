import wordpressPlugin from '@wordpress/eslint-plugin';
import babelParser from '@babel/eslint-parser';

export default [
  {
    ignores: ['node_modules', 'dist', 'build'],
  },
  ...wordpressPlugin.configs.recommended,
  {
    files: ['**/*.js', '**/*.jsx'],
    languageOptions: {
      parser: babelParser,
      parserOptions: {
        requireConfigFile: false,
        ecmaVersion: 'latest',
        sourceType: 'module',
        ecmaFeatures: {
          jsx: true,
        },
      },
      globals: {
        lodash: 'readonly',
        React: 'readonly',
        ReactDOM: 'readonly',
        wp: 'readonly',
      },
    },
    settings: {
      react: {
        version: '18.3.1',
      },
    },
    rules: {
      // Prettier formatting
      'prettier/prettier': ['error'],

      // React rules
      'react/prop-types': 'off',

      // Import rules
      'import/no-extraneous-dependencies': ['error', { devDependencies: true }],

      // WordPress i18n rules
      '@wordpress/i18n-text-domain': ['error', { allowedTextDomain: ['amnesty', 'default'] }],

      // Naming rules
      'no-underscore-dangle': [
        'error',
        {
          allow: ['_embedded', '_EventStartDate', '_EventEndDate', '_VenueCity', '_OrganizerEmail'],
        },
      ],

      // Disable some defaults
      'prefer-destructuring': 'off',
    },
  },
];
