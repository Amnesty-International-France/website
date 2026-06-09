# Interactive Map

This project plugin provides the `create-block/interactive-map` Gutenberg block.
It renders a Leaflet map used to search and display Amnesty France local
structures.

## Runtime Role

The plugin registers block metadata from `build/` on WordPress `init`. It uses
WordPress' block metadata collection APIs when available and falls back to
registering each block type from the generated manifest.

Runtime rendering is handled by `build/interactive-map/render.php`, generated
from `src/interactive-map/render.php`.

The block:

- loads a GeoJSON layer for departments;
- uses Leaflet for map display and markers;
- queries theme REST endpoints for local structures and geocoding;
- supports editor-configured map colors, tile layer, the current France country
  preset, and optional vignettes.

`block.json` declares `multiple: false`, and the public script initializes the
first `.interactive-map` element found on the page.

## Source And Build Output

Source files live under `src/interactive-map`:

- `block.json`: block metadata, attributes, editor assets, and view assets;
- `edit.js`: inspector controls in the block editor;
- `render.php`: server-rendered markup and data attributes;
- `view.js`: public map initialization and interactions;
- `Api.js`: marker and geocoding fetches;
- `MapController.js`: Leaflet map, layers, markers, and view switches;
- `Ui.js`: public UI state and marker result rendering;
- `style.scss`: block styles.

Generated files live under `build/` and are committed because WordPress loads
the block from that directory.

## Data Dependencies

The block currently depends on theme REST routes:

- `amnesty/v1/local-structures-search`;
- `amnesty/v1/geocode-proxy`.

The rendered block ignores the editable `apiEndpoint` attribute and uses those
project endpoints directly. The source contains a TODO noting that this coupling
should move out of the plugin.

`block.json` also declares `mapZoomSnap`, and `view.js` reads
`data-map-zoom-snap`, but the current PHP render template does not output that
data attribute. As a result, the configured value is not currently transmitted
to Leaflet at runtime.

Static map data lives under:

- `assets/geojson/france-departements.geojson`, used by the runtime GeoJSON
  layer;
- `assets/markers/*.json`, present in the repository but not referenced by the
  current runtime code.

## Build Commands

Run these commands from `wp-content/plugins/interactive-map`:

```bash
yarn install
yarn build
yarn start
yarn lint:js
yarn lint:css
```

The plugin uses `@wordpress/scripts` and Leaflet. A production build updates the
committed `build/` files.

## Change Conventions

When changing the block:

- edit `src/interactive-map` first;
- rebuild and include the matching `build/` output;
- verify the block editor can insert or edit the block;
- verify the public page renders a non-empty map and can fetch markers;
- keep REST endpoint contract changes synchronized with the theme routes.
