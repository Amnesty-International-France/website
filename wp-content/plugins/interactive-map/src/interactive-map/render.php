<?php

declare(strict_types=1);

$vignettes = $attributes['vignettes'] ?? [];
$full_geojson_url = '';
$geojson_source = $attributes['geoJsonUrl'];

if (!empty($attributes['geoJsonUrl'])) {
	$geojson_source = $attributes['geoJsonUrl'];
	$is_absolute_url = 'http' === parse_url($geojson_source, PHP_URL_SCHEME) || 'https' === parse_url($geojson_source, PHP_URL_SCHEME);

	if ($is_absolute_url) {
		$full_geojson_url = $geojson_source;
		return;
	}

	$full_geojson_url = INTERACTIVE_MAP_URL . ltrim($geojson_source, '/');
}
?>

<div
    <?php echo get_block_wrapper_attributes(['class' => 'interactive-map']); ?>
	data-geojson-url="<?php echo esc_url($full_geojson_url); ?>"
	data-tile-layer-url="<?php echo esc_url($attributes['tileLayerUrl']); ?>"
	data-api-endpoint="<?php echo esc_url($attributes['apiEndpoint']); ?>"
	data-show-vignettes="<?php echo esc_attr($attributes['showVignettes'] ? 'true' : 'false'); ?>"
	data-map-center-lat="<?php echo esc_attr($attributes['mapCenterLat']); ?>"
	data-map-center-lng="<?php echo esc_attr($attributes['mapCenterLng']); ?>"
	data-map-default-zoom="<?php echo esc_attr($attributes['mapDefaultZoom']); ?>"
>
    <style>
        .interactive-map { background-color: <?php echo esc_attr($attributes['mapBackgroundColor']); ?>; }
        .interactive-map .leaflet-interactive { fill: <?php echo esc_attr($attributes['defaultPathColor']); ?>; }
        .interactive-map .leaflet-interactive--highlighted { fill: <?php echo esc_attr($attributes['hoverPathColor']); ?> !important; }
    </style>

    <div class="interactive-map__container">
        <button type="button" class="interactive-map__back-button">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 100 100"><g><path fill="currentColor" d="M71.278,95.103c-1.113,0-2.226-0.425-3.075-1.273L24.374,50l43.83-43.83c1.699-1.697,4.451-1.697,6.15,0c1.698,1.699,1.698,4.451,0,6.15L36.672,50l37.681,37.68c1.698,1.699,1.698,4.451,0,6.15C73.504,94.679,72.391,95.103,71.278,95.103z"></path></g></svg>
            Retour aux départements
        </button>

        <?php if ($attributes['showVignettes']) : ?>
            <div class="interactive-map__selectors">
                <div class="interactive-map__selector-list">
                    <?php foreach ($vignettes as $index => $view) : ?>
                        <div class="interactive-map__selector <?php echo $index === 0 ? 'interactive-map__selector--active' : ''; ?>">
                            <p class="interactive-map__selector-label"><?php echo esc_html($view['label']); ?></p>
                            <div
                                    class="interactive-map__selector-thumbnail"
                                    data-view-id="<?php echo esc_attr($view['id']); ?>"
                                    data-lat="<?php echo esc_attr($view['lat']); ?>"
                                    data-lng="<?php echo esc_attr($view['lng']); ?>"
                                    data-zoom="<?php echo esc_attr($view['zoom']); ?>"
                            >
                                <div class="interactive-map__selector-svg-wrapper">
                                    <?php if(isset($view['svg'])) { echo $view['svg']; } ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
