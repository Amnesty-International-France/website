# Frontend Toolchain

This document describes the `private` subsystem: frontend sources, asset
generation, local WordPress tooling, and frontend quality checks.

## Role

`private` is the source of truth for the main theme frontend assets. It contains:

- JavaScript modules used by the public site, editor, admin, and Turnstile forms;
- SCSS sources for theme, editor, admin, blocks, `Mon Espace`, WooCommerce, and
  shared components;
- static images, fonts, and frontend-only assets;
- Webpack, Babel, PostCSS, ESLint, Stylelint, Vitest, TypeScript, Yarn, and
  `wp-env` configuration;
- helper scripts used by local installation, linting, translation, and external
  test workflows.

The generated runtime assets are written into
`wp-content/themes/humanity-theme/assets`. When frontend source changes affect
the browser output, the corresponding built assets should be regenerated and
committed with the source changes.

## Directory Layout

Important directories and files:

- `src/scripts/`: JavaScript entry points and modules.
- `src/scripts/modules/`: public-site behavior modules initialized by
  `App.js`, plus the dedicated Turnstile form guard module.
- `src/scripts/editor/`: Gutenberg block registrations, editor plugins, block
  styles, and editor-side behavior.
- `src/scripts/admin/`: WordPress admin behavior.
- `src/styles/`: SCSS entry points and partials.
- `src/static/`: static assets copied to the theme asset directory.
- `tests/`: Vitest tests. The current coverage is focused on Turnstile.
- `webpack.config.js`: frontend build configuration.
- `package.json` and `yarn.lock`: frontend dependency graph and scripts.
- `tsconfig.json`: targeted JavaScript type checking configuration.
- `.wp-env.json`: local WordPress environment definition.
- `bin/`: helper scripts for install, lint, translations, and external tests.

## Runtime Boundary

`private` is not loaded by WordPress directly. WordPress consumes the generated
files from:

- `wp-content/themes/humanity-theme/assets/scripts`;
- `wp-content/themes/humanity-theme/assets/styles`;
- `wp-content/themes/humanity-theme/assets/images`;
- `wp-content/themes/humanity-theme/assets/fonts`.

The theme enqueues those generated files from PHP. This means changes under
`private/src` are incomplete until the relevant Webpack build has been run.

## Build Pipeline

The build is custom Webpack tooling, not `@wordpress/scripts`.

Main commands from `private/package.json`:

- `yarn build:dev`: development build;
- `yarn build:prod`: production build;
- `yarn build`: production build alias;
- `yarn build:static`: static asset copy build;
- `yarn watch:dev`: development watch mode;
- `yarn watch:prod`: production watch mode;
- `yarn watch`: watch alias.

Webpack compiles from `private/src` and writes into
`../wp-content/themes/humanity-theme/assets`. The output path is relative to the
`private` directory.

The build stack includes:

- Webpack 5 for bundling;
- `esbuild-loader` for JavaScript transpilation and production minification;
- Babel configuration for legacy syntax support and React JSX transforms;
- Sass, PostCSS, Autoprefixer, and `postcss-pxtorem` for styles;
- `mini-css-extract-plugin` for CSS extraction;
- `copy-webpack-plugin` for static assets;
- `@svgr/webpack` for imported SVG components.

The configured Node version is `v20.9.0`. Yarn uses the `node-modules` linker.

## Webpack Entries

The main entries are:

- `bundle`: public-site application, sourced from `src/scripts/App.js`.
- `turnstile`: standalone Turnstile client guard, sourced from
  `src/scripts/turnstile.js`.
- `blocks`: Gutenberg block/editor registrations, sourced from
  `src/scripts/blocks.js`.
- `editor`: editor styles, sourced from `src/scripts/editor.js`.
- `admin`: admin scripts and styles, sourced from `src/scripts/admin.js`.

The build supports `--env entry=<name>` to compile one entry. `--env entry=static`
runs only the static asset copy path.

JavaScript files are emitted under `assets/scripts`. Extracted CSS is emitted
under `assets/styles`.

## JavaScript Architecture

`src/scripts/App.js` is the main public frontend application. It imports
`src/styles/app.scss`, frontend polyfills, and all public interaction modules,
then initializes them in a single `App()` function.

The application is exposed to `window.App.default()` through Webpack
`expose-loader`. The theme calls that global from footer inline JavaScript after
the generated `bundle` script has been enqueued.

Common public modules handle:

- navigation, headers, overlays, pop-ins, language selectors, and banners;
- filters, sliders, tabs, carousels, and table-of-contents behavior;
- petition, urgent action, newsletter, donation, and Jetpack form behavior;
- `Mon Espace` menus and mobile interactions;
- share, tracking feedback, localization, iframes, and responsive helpers.

The `turnstile` entry is intentionally separate from `bundle`. It must be loaded
before the Cloudflare Turnstile API script so global callbacks and submit guards
exist before Cloudflare renders or validates protected forms.

Webpack externals assume these globals are provided by WordPress or the theme:

- `lodash`;
- `React`;
- `ReactDOM`;
- `wp.i18n`.

## Styles Architecture

The main SCSS entry is `src/styles/app.scss`. It imports the project styling in
this broad order:

- design variables, grid settings, functions, and mixins;
- vendor styles such as Flickity;
- base resets, typography, images, containers, overlays, and Jetpack forms;
- layout partials for header, navigation, footer, hero, and banners;
- components such as lists, media, social links, forms, grids, modals, filters,
  donation tools, and back-to-top controls;
- `Mon Espace` styles;
- block and core-block styles;
- block pattern styles;
- page-specific styles;
- WooCommerce styles;
- utility helper classes;
- import-specific styles.

Additional SCSS entries are:

- `admin.scss`: admin interface styles;
- `editor.scss`: editor styles;
- `gutenberg.scss`: Gutenberg block/editor styles loaded by the `blocks` entry.

Webpack sets `css-loader` with `url: false`, so asset URLs inside CSS are not
rewritten by the loader. Static images and fonts are copied from `src/static`.

## Local WordPress Environment

`private/.wp-env.json` defines a local WordPress environment with:

- the project theme mounted from `../wp-content/themes/humanity-theme`;
- CMB2 and CMB2-related plugins installed from WordPress.org or GitHub;
- an `afterStart` lifecycle script that activates `humanity-theme`.

The package script `yarn env` delegates to `@wordpress/env` through `yarn dlx`.
This is local environment tooling only; the main frontend build does not use
`@wordpress/scripts`.

## Tests And Type Checking

Current frontend checks include:

- `yarn test`: runs Vitest in `jsdom`;
- `yarn test:e2e`: runs Playwright end-to-end tests against the local WordPress
  E2E environment;
- `yarn typecheck:turnstile`: runs TypeScript in `allowJs`/`checkJs` mode for
  `src/scripts/modules/turnstile.js`.

The Vitest configuration includes `tests/**/*.test.js`. The current tests are
scoped to the Turnstile module.

The TypeScript configuration is intentionally narrow. It enables strict checking
for the Turnstile module without converting the broader JavaScript codebase to
TypeScript.

Playwright tests live under `tests/e2e`. They use `tests/e2e/.wp-env.e2e.json`
and a local-only E2E support plugin that provides Cloudflare Turnstile dummy
keys for the WordPress test environment on `http://localhost:8898`.
Deterministic tests mock Cloudflare's API script and run on every PR. The real
Cloudflare dummy-key smoke test is opt-in:

```bash
yarn env:e2e:start
./tests/e2e/support/seed-wordpress.sh
yarn test:e2e
RUN_CLOUDFLARE_SMOKE=1 yarn test:e2e tests/e2e/turnstile-cloudflare-dummy.spec.mjs
yarn env:e2e:stop
```

Before running E2E tests for the first time, install Chromium:

```bash
yarn playwright install chromium
```

## Linting And Formatting

Main quality commands:

- `yarn lint:scripts`: ESLint over `src/`;
- `yarn lint:styles`: Stylelint over `src/**/*.scss`;
- `yarn lint`: runs script and style linting;
- `yarn prettier --write <paths>`: applies the configured Prettier formatting
  when formatting changes are needed.

ESLint uses Airbnb base rules, React rules, WordPress i18n rules, and Prettier.
Stylelint uses SCSS rules and project-specific exceptions.

## Helper Scripts

The `bin/` directory contains shell helpers:

- `install.sh`: Composer install, Node setup through `nvm`, Corepack activation,
  and Yarn install under `private`;
- `lint.sh`: Composer linting, frontend linting, and optional shell linting;
- `lang.sh`: translation file update workflow;
- `run-tests.sh`: Ghost Inspector CLI workflow.

These scripts are operational helpers rather than WordPress runtime code.

## Change Conventions

When changing frontend behavior:

- edit source files under `private/src`, not generated files first;
- rebuild the affected Webpack entry and include generated theme assets when
  they change;
- keep generated asset diffs scoped to the related source change;
- keep Turnstile code in the dedicated `turnstile` entry when the code must run
  before the Cloudflare API;
- avoid migrating this build to `@wordpress/scripts` unless that decision is
  made explicitly for the whole subsystem;
- run the smallest relevant checks first, then broaden checks when touching
  shared entries such as `bundle` or `app.scss`.

Recommended checks by change type:

- Turnstile changes: `yarn test`, `yarn typecheck:turnstile`,
  `yarn test:e2e --grep-invert @cloudflare-smoke`, and the relevant Webpack
  build;
- JavaScript module changes: `yarn lint:scripts` and the relevant Webpack build;
- SCSS changes: `yarn lint:styles` and the relevant Webpack build;
- shared entry changes: `yarn lint`, `yarn test`, and `yarn build:prod`.
