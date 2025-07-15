import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  TextControl,
  ToggleControl,
  SelectControl,
  Button,
  TextareaControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

const COUNTRY_PRESETS = [
  {
    label: "France",
    value: "fr",
    geoJsonUrl: "assets/geojson/france-departements.geojson",
    center: [46.5, 1.8],
    zoom: 5.5,
  },
];

const TILE_LAYER_OPTIONS = [
  {
    label: "CartoDB Voyager",
    value:
      "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
  },
  {
    label: "OpenStreetMap",
    value: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
  },
  {
    label: "Stadia Stamen Toner",
    value: "https://tiles.stadiamaps.com/tiles/stamen_toner/{z}/{x}/{y}{r}.png",
  },
];

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();

  const {
    countrySlug,
    tileLayerUrl,
    apiEndpoint,
    mapBackgroundColor,
    defaultPathColor,
    hoverPathColor,
    showVignettes,
    vignettes,
  } = attributes;

  const onCountryChange = (slug) => {
    const selectedCountry = COUNTRY_PRESETS.find((c) => c.value === slug);
    if (selectedCountry) {
      setAttributes({
        countrySlug: slug,
        geoJsonUrl: selectedCountry.geoJsonUrl,
        mapCenterLat: selectedCountry.center[0],
        mapCenterLng: selectedCountry.center[1],
        mapDefaultZoom: selectedCountry.zoom,
      });
    }
  };

  const updateVignette = (index, key, value) => {
    const newVignettes = [...vignettes];
    newVignettes[index] = { ...newVignettes[index], [key]: value };
    setAttributes({ vignettes: newVignettes });
  };

  const addVignette = () => {
    const newVignettes = [
      ...vignettes,
      {
        id: `view-${vignettes.length + 1}`,
        label: "Nouvelle Vue",
        lat: 47,
        lng: 2.2,
        zoom: 5.5,
        svg: "<svg>...</svg>",
      },
    ];
    setAttributes({ vignettes: newVignettes });
  };

  const removeVignette = (index) => {
    const newVignettes = vignettes.filter((_, i) => i !== index);
    setAttributes({ vignettes: newVignettes });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Map Settings", "interactive-map")}>
          <SelectControl
            label={__("Country", "interactive-map")}
            value={countrySlug}
            options={COUNTRY_PRESETS}
            onChange={onCountryChange}
          />
          <SelectControl
            label={__("Tile Layer Provider", "interactive-map")}
            value={tileLayerUrl}
            options={TILE_LAYER_OPTIONS}
            onChange={(value) => setAttributes({ tileLayerUrl: value })}
          />
        </PanelBody>

        <PanelBody title={__("Data & API", "interactive-map")}>
          <TextControl
            label={__("Markers API Endpoint", "interactive-map")}
            value={apiEndpoint}
            onChange={(value) => setAttributes({ apiEndpoint: value })}
            help={__(
              "The endpoint that receives coordinates and returns markers.",
              "interactive-map",
            )}
          />
        </PanelBody>

        <PanelBody title={__("Appearance", "interactive-map")}>
          <TextControl
            label={__("Background Color", "interactive-map")}
            value={mapBackgroundColor}
            onChange={(value) => setAttributes({ mapBackgroundColor: value })}
          />
          <TextControl
            label={__("Default Department Color", "interactive-map")}
            value={defaultPathColor}
            onChange={(value) => setAttributes({ defaultPathColor: value })}
          />
          <TextControl
            label={__("Hover Department Color", "interactive-map")}
            value={hoverPathColor}
            onChange={(value) => setAttributes({ hoverPathColor: value })}
          />
        </PanelBody>

        <PanelBody
          title={__("Vignettes", "interactive-map")}
          initialOpen={false}
        >
          <ToggleControl
            label={__("Show Vignettes", "interactive-map")}
            checked={showVignettes}
            onChange={(value) => setAttributes({ showVignettes: value })}
          />
          {showVignettes && (
            <div style={{ marginTop: "20px" }}>
              {vignettes.map((vignette, index) => (
                <div
                  key={index}
                  style={{
                    border: "1px solid #ccc",
                    padding: "10px",
                    marginBottom: "10px",
                  }}
                >
                  <TextControl
                    label={__("Label", "interactive-map")}
                    value={vignette.label}
                    onChange={(value) => updateVignette(index, "label", value)}
                  />
                  <TextControl
                    label={__("View ID", "interactive-map")}
                    value={vignette.id}
                    onChange={(value) => updateVignette(index, "id", value)}
                  />
                  <div style={{ display: "flex", gap: "10px" }}>
                    <TextControl
                      label={__("Lat", "interactive-map")}
                      type="number"
                      value={vignette.lat}
                      onChange={(value) =>
                        updateVignette(index, "lat", parseFloat(value))
                      }
                    />
                    <TextControl
                      label={__("Lng", "interactive-map")}
                      type="number"
                      value={vignette.lng}
                      onChange={(value) =>
                        updateVignette(index, "lng", parseFloat(value))
                      }
                    />
                    <TextControl
                      label={__("Zoom", "interactive-map")}
                      type="number"
                      value={vignette.zoom}
                      onChange={(value) =>
                        updateVignette(index, "zoom", parseFloat(value))
                      }
                    />
                  </div>
                  <TextareaControl
                    label={__("SVG Code", "interactive-map")}
                    value={vignette.svg}
                    onChange={(value) => updateVignette(index, "svg", value)}
                    rows={5}
                  />
                  <Button
                    isDestructive
                    isSecondary
                    onClick={() => removeVignette(index)}
                    style={{ marginTop: "10px" }}
                  >
                    {__("Remove Vignette", "interactive-map")}
                  </Button>
                </div>
              ))}
              <Button
                variant="primary"
                onClick={addVignette}
                style={{ marginTop: "20px" }}
              >
                {__("Add Vignette", "interactive-map")}
              </Button>
            </div>
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <p>Carte Interactive</p>
        <p style={{ fontSize: "12px", fontStyle: "italic" }}>
          {__("Configure the map in the sidebar.", "interactive-map")}
        </p>
      </div>
    </>
  );
}
