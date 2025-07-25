<?php

declare(strict_types=1);

$vignettes = $attributes['vignettes'] ?? [];
$full_geojson_url = '';
$geojson_source = $attributes['geoJsonUrl'];

if (!empty($attributes['geoJsonUrl'])) {
    $is_absolute_url = 'http' === parse_url($geojson_source, PHP_URL_SCHEME) || 'https' === parse_url($geojson_source, PHP_URL_SCHEME);

    if ($is_absolute_url) {
        $full_geojson_url = $geojson_source;
    } else {
        $full_geojson_url = defined('INTERACTIVE_MAP_URL') ? INTERACTIVE_MAP_URL . ltrim($geojson_source, '/') : $geojson_source;
    }
}

$custom_local_structures_api_endpoint = rest_url( 'amnesty/v1/local-structures-search' );

$geocode_proxy_api_endpoint = rest_url( 'amnesty/v1/geocode-proxy' );

?>

<div
    <?php echo get_block_wrapper_attributes(['class' => 'interactive-map']); ?>
    data-geojson-url="<?php echo esc_url($full_geojson_url); ?>"
    data-tile-layer-url="<?php echo esc_url($attributes['tileLayerUrl']); ?>"
    data-api-endpoint="<?php echo esc_url($custom_local_structures_api_endpoint); ?>" data-geocode-proxy-endpoint="<?php echo esc_url($geocode_proxy_api_endpoint); ?>" data-show-vignettes="<?php echo esc_attr($attributes['showVignettes'] ? 'true' : 'false'); ?>"
    data-map-center-lat="<?php echo esc_attr($attributes['mapCenterLat']); ?>"
    data-map-center-lng="<?php echo esc_attr($attributes['mapCenterLng']); ?>"
    data-map-default-zoom="<?php echo esc_attr($attributes['mapDefaultZoom']); ?>"
>
    <style>
        .interactive-map { background-color: <?php echo esc_attr($attributes['mapBackgroundColor']); ?>; }
        .interactive-map .leaflet-interactive { fill: <?php echo esc_attr($attributes['defaultPathColor']); ?>; }
        .interactive-map .leaflet-interactive--highlighted { fill: <?php echo esc_attr($attributes['hoverPathColor']); ?> !important; }
    </style>

    <div class="interactive-map__wrapper">
        <div class="interactive-map__search-container">
            <div class="interactive-map__search-form">
                <div class="input-or">
                    <input id="map-search-input" name="location" type="text" placeholder="Code postal ou ville" class="interactive-map__search-input">
                    <button type="button" id="map-search-button" class="interactive-map__search-button">
                        <?php echo file_get_contents(get_template_directory() . '/assets/images/icon-search.svg'); ?>
                    </button>
                    <span>ou</span>
                </div>
                <button class="btn btn--yellow geolocate-me">Me Géolocaliser</button>
            </div>
            <div id="map-search-results" class="interactive-map__search-results"></div>
        </div>

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
</div>

<div class="join-and-agenda">
    <div class="join">
        <p class="join-title">Pas de groupe local près de chez vous ?</p>
        <p class="join-subtitle">Créez-en un !</p>
        <div class='custom-button-block center'>
            <a href="/rejoindre-un-groupe" target="_blank" rel="noopener noreferrer" class="custom-button">
                <div class='content bg-yellow medium'>
                    <div class="icon-container">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth="1.5"
                            stroke="currentColor"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                    <div class="button-label">Créer un groupe</div>
                </div>
            </a>
        </div>
    </div>
    <div class="agenda">
        <p class="agenda-title">Agenda</p>
        <p class="agenda-subtitle">Consultez les événements à venir</p>
        <div class='custom-button-block center'>
            <a href="/agenda" target="_blank" rel="noopener noreferrer" class="custom-button">
                <div class='content bg-yellow medium'>
                    <div class="icon-container">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth="1.5"
                            stroke="currentColor"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                    <div class="button-label">Voir l'agenda</div>
                </div>
            </a>
        </div>
    </div>
</div>
