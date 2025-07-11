import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// TODO: see to deport some data, functionality in different file services, adopt functional programing
document.addEventListener('DOMContentLoaded', () => {
    const blockContainer = document.querySelector('.interactive-map');
    if (!blockContainer) return;

    const {
        geojsonUrl,
        tileLayerUrl,
        apiEndpoint,
        showVignettes,
        mapCenterLat,
        mapCenterLng,
        mapDefaultZoom
    } = blockContainer.dataset;

    let mapCenter = [parseFloat(mapCenterLat), parseFloat(mapCenterLng)];
    let mapZoom = parseFloat(mapDefaultZoom);

    // --- Essential validations ---
    if (!geojsonUrl || !tileLayerUrl) {
        console.error('Essential map URLs (geojsonUrl or tileLayerUrl) are not defined in the block\'s data attributes.');
        return;
    }
    const defaultSelector = blockContainer.querySelector('.interactive-map__selector-thumbnail');
    if (defaultSelector) {
        mapCenter = [
            parseFloat(defaultSelector.dataset.lat),
            parseFloat(defaultSelector.dataset.lng)
        ];
        mapZoom = parseFloat(defaultSelector.dataset.zoom);
    }
    const mapContainer = blockContainer.querySelector('.interactive-map__container');
    if (!mapContainer) return;
    const backButton = blockContainer.querySelector('.interactive-map__back-button');
    const selectorsContainer = blockContainer.querySelector('.interactive-map__selectors');

    // --- Map configuration ---
    const markerLayerGroup = L.layerGroup();
    const customMarkerIcon = L.divIcon({
        className: 'interactive-map__marker-icon',
        html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill-rule="evenodd" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/></svg>`,
        iconSize: [24, 32],
    });

    const tileLayer = L.tileLayer(`https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png`, {
        attribution: '&copy; OpenStreetMap contributors',
        subdomains: 'abcd',
        maxZoom: 20
    });

    const geoJsonLayer = L.geoJSON();
    const map = L.map(mapContainer, {
        center: mapCenter,
        zoom: mapZoom,
        zoomControl: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false,
        dragging: false,
    });
    L.control.zoom({ position: 'topright' }).addTo(map);

    // --- Main functions ---
    /**
     * Calls the API with geofences to get the markers.
     * @param {L.LatLngBounds} bounds - Geographical limits.
     * @returns {Promise<Array>} - Returns markers arrays.
     */
    const getMarkersForBounds = async (bounds) => {
        if (!apiEndpoint) {
            console.warn('No API endpoint is configured in the block settings.');
            return [];
        }

        const payload = {
            south: bounds.getSouth(),
            west: bounds.getWest(),
            north: bounds.getNorth(),
            east: bounds.getEast(),
        };

        try {
            // TODO: Http method must be configurable, can be GET or POST, incase of GET pass the payload in query params
            // TODO: create a custom rest api in future
            const response = await fetch(apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (!response.ok) {
                console.error(`API Error: ${response.status} ${response.statusText}`);
                return [];
            }
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch markers from API:', error);
            return [];
        }
    };

    /**
     * Switches to region view, displays tiles and markers.
     * @param {L.LatLngBounds} bounds - The geographic boundaries of the area to be displayed.
     */
    const switchToRegionView = async (bounds) => {
        blockContainer.classList.add('interactive-map--region-view');
        markerLayerGroup.clearLayers();

        const regionMarkers = await getMarkersForBounds(bounds);

        regionMarkers.forEach(markerInfo => {
            // TODO: Data returned by different API for markerInfo can use differents properties so all these properties must be configurable or find a better way
            if (markerInfo._geoloc) {
                const latLng = [markerInfo._geoloc.lat, markerInfo._geoloc.lng];
                const popupContent = `<a href="${markerInfo.url || '#'}" class="interactive-map__popup-link"><div class="interactive-map__popup-image-wrapper"><img src="${markerInfo.image || ''}" alt="" class="interactive-map__popup-image" style="display: ${markerInfo.image ? 'block' : 'none'};" loading="lazy"></div><div class="interactive-map__popup-content"><p class="interactive-map__popup-city"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" ><path fill-rule="evenodd" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/> </svg><span class="interactive-map__popup-city-name">${markerInfo.city || ''}</span></p><h3 class="interactive-map__popup-title">${markerInfo.title || ''}</h3><div class="interactive-map__popup-facets"><div class="interactive-map__popup-facet-group"><span class="interactive-map__popup-facet">${markerInfo.facet || ''}</span><span class="interactive-map__popup-subfacet">${markerInfo.subfacet || ''}</span></div></div></div></a>`;
                L.marker(latLng, { icon: customMarkerIcon })
                    .bindPopup(popupContent, { className: 'interactive-map__popup', offset: [0, -25] })
                    .addTo(markerLayerGroup);
            }
        });

        map.removeLayer(geoJsonLayer);
        map.addLayer(tileLayer);
        map.addLayer(markerLayerGroup);
        map.fitBounds(bounds, { padding: [50, 50] });

        map.dragging.enable();
        map.scrollWheelZoom.enable();
        map.doubleClickZoom.enable();
    };

    /**
     * Switches to country view, displays tiles and markers.
     */
    const switchToCountryView = () => {
        blockContainer.classList.remove('interactive-map--region-view');
        markerLayerGroup.clearLayers();
        map.removeLayer(tileLayer);

        map.setView(mapCenter, mapZoom, { animate: false });
        map.addLayer(geoJsonLayer);

        map.dragging.disable();
        map.scrollWheelZoom.disable();
        map.doubleClickZoom.disable();
    };

    /**
     * Configure interactions for each department on the map.
     */
    const onEachFeature = (feature, layer) => {
        const departmentName = feature.properties.nom || 'N/A';
        const departmentCode = feature.properties.code || '';
        layer.bindTooltip(`${departmentName.toUpperCase()} (${departmentCode})`, {
            className: 'interactive-map__department-tooltip',
            permanent: false,
            direction: 'auto',
            opacity: 1,
        });

        layer.on({
            mouseover: (e) => e.target.getElement().classList.add('leaflet-interactive--highlighted'),
            mouseout: (e) => e.target.getElement().classList.remove('leaflet-interactive--highlighted'),
            click: (e) => {
                const bounds = e.target.getBounds();
                switchToRegionView(bounds);
            },
        });
    };

    // --- Initial loading and event configuration ---
    fetch(geojsonUrl)
        .then(response => {
            if (!response.ok) throw new Error(`Failed to load GeoJSON from ${geojsonUrl}`);
            return response.json();
        })
        .then(data => {
            geoJsonLayer.addData(data);
            geoJsonLayer.eachLayer(layer => onEachFeature(layer.feature, layer));
            map.addLayer(geoJsonLayer);
            map.invalidateSize();
        })
        .catch(error => {
            console.error(error);
            mapContainer.innerHTML = '<p style="font-weight: bold;">An error occurred while displaying the map.</p>';
        });

    // --- Thumbnail management ---
    if ('true' === showVignettes && selectorsContainer) {
        const selectors = blockContainer.querySelectorAll('.interactive-map__selector-thumbnail');
        selectors.forEach((selector, index) => {
            selector.addEventListener('click', (e) => {
                const currentSelector = e.currentTarget;

                // Active class logic
                const allSelectorParents = blockContainer.querySelectorAll('.interactive-map__selector');
                allSelectorParents.forEach(s => s.classList.remove('interactive-map__selector--active'));
                currentSelector.parentElement.classList.add('interactive-map__selector--active');

                if (index === 0) {
                    switchToCountryView();

                    return;
                }

                const lat = parseFloat(currentSelector.dataset.lat);
                const lng = parseFloat(currentSelector.dataset.lng);
                const zoom = parseFloat(currentSelector.dataset.zoom);

                if (!isNaN(lat) && !isNaN(lng) && !isNaN(zoom)) {
                    // For thumbnails, we zoom in first, then we get the bounds for the API.
                    map.setView([lat, lng], zoom, { animate: false });
                    const bounds = map.getBounds();
                    switchToRegionView(bounds);
                }
            });
        });
    } else if (selectorsContainer) {
        selectorsContainer.style.display = 'none';
    }

    // --- Back button ---
    backButton.addEventListener('click', () => {
        switchToCountryView();

        const allSelectorParents = blockContainer.querySelectorAll('.interactive-map__selector');
        allSelectorParents.forEach(s => s.classList.remove('interactive-map__selector--active'));
        if (allSelectorParents.length > 0) {
            allSelectorParents[0].classList.add('interactive-map__selector--active');
        }
    });

    // --- Initial state ---
    map.dragging.disable();
    map.scrollWheelZoom.disable();
});
