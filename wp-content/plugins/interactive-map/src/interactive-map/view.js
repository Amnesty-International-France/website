import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import {MapController} from './MapController.js';
import {Api} from './Api.js';
import {UI} from './Ui.js';

document.addEventListener('DOMContentLoaded', () => {
  // --- Init and configure ---
  const blockContainer = document.querySelector('.interactive-map');
  if (!blockContainer) {
    return;
  }

  const {
    geojsonUrl,
    tileLayerUrl,
    apiEndpoint,
    geocodeProxyEndpoint,
    showVignettes,
    mapCenterLat,
    mapCenterLng,
    mapDefaultZoom,
    mapZoomSnap,
  } = blockContainer.dataset;

  if (!geojsonUrl || !tileLayerUrl) {
    console.error(
      'Les URLs essentielles (geojsonUrl, tileLayerUrl) sont manquantes.'
    );
    return;
  }

  const elements = {
    blockContainer,
    mapContainer: blockContainer.querySelector(
      '.interactive-map__container'
    ),
    backButton: blockContainer.querySelector(
      '.interactive-map__back-button'
    ),
    selectorsContainer: blockContainer.querySelector(
      '.interactive-map__selectors'
    ),
    searchInput: blockContainer.querySelector('#map-search-input'),
    searchButton: blockContainer.querySelector('#map-search-button'),
    searchResultsDiv: blockContainer.querySelector('#map-search-results'),
    geolocateButton: blockContainer.querySelector('.geolocate-me'),
  };

  const firstSelector = elements.selectorsContainer?.querySelector(
    '.interactive-map__selector-thumbnail'
  );
  const initialConfig = {
    center: [parseFloat(mapCenterLat), parseFloat(mapCenterLng)],
    zoom: parseFloat(mapDefaultZoom),
    zoomSnap: parseFloat(mapZoomSnap),
    tileLayerUrl,
  };

  if (firstSelector) {
    initialConfig.center = [
      parseFloat(firstSelector.dataset.lat),
      parseFloat(firstSelector.dataset.lng),
    ];
    initialConfig.zoom = parseFloat(firstSelector.dataset.zoom);
  }

  const ui = new UI(elements);
  const api = new Api(apiEndpoint, geocodeProxyEndpoint);
  const mapController = new MapController(
    elements.mapContainer,
    initialConfig
  );

  const departmentBoundsMap = new Map();

  // --- Application logic ---
  const switchToRegionView = async (
    bounds,
    center = null,
    radius = null,
    isDepartment = false
  ) => {
    ui.toggleView(true);
    ui.showLoadingMessage();

    const markers = await api.getMarkersForBounds(bounds, center, radius, isDepartment);

    mapController.switchToRegionView();
    mapController.displayMarkers(markers);
    ui.displayMarkersAsCards(markers);

    mapController.fitBoundsToMarkers(markers, bounds);
  };

  const switchToCountryView = () => {
    ui.toggleView(false);
    mapController.switchToCountryView(
      initialConfig.center,
      initialConfig.zoom
    );
    ui.showInitialMessage();
    ui.updateActiveVignette(firstSelector);
  };

  const onEachFeature = (feature, layer) => {
    const departmentName = feature.properties.nom || 'N/A';
    const departmentCode = feature.properties.code_insee || '';

    const normalizedName = departmentName
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
    departmentBoundsMap.set(normalizedName, layer.getBounds());
    departmentBoundsMap.set(
      departmentCode.toLowerCase(),
      layer.getBounds()
    );

    layer.bindTooltip(
      `${departmentName.toUpperCase()} (${departmentCode})`,
      {
        className: 'interactive-map__department-tooltip',
        permanent: false,
        direction: 'auto',
        opacity: 1,
      }
    );

    layer.on({
      mouseover: (e) =>
        e.target
          .getElement()
          .classList.add('leaflet-interactive--highlighted'),
      mouseout: (e) =>
        e.target
          .getElement()
          .classList.remove('leaflet-interactive--highlighted'),
      click: (e) => {
        const bounds = e.target.getBounds();
        switchToRegionView(bounds, null, null, true);
      },
    });
  };

  if (showVignettes === 'true' && elements.selectorsContainer) {
    const selectors = elements.selectorsContainer.querySelectorAll(
      '.interactive-map__selector-thumbnail'
    );
    selectors.forEach((selector, index) => {
      selector.addEventListener('click', (e) => {
        ui.updateActiveVignette(selector);
        if (index === 0) {
          switchToCountryView();
        } else {
          const lat = parseFloat(selector.dataset.lat);
          const lng = parseFloat(selector.dataset.lng);
          if (!isNaN(lat) && !isNaN(lng)) {
            const center = { lat, lng };
            const radiusKm = 30;
            const centerLatLng = L.latLng(lat, lng);
            const bounds = centerLatLng.toBounds(radiusKm * 1000);

            switchToRegionView(bounds, center, radiusKm, false);
          }
        }
      });
    });
  } else if (elements.selectorsContainer) {
    elements.selectorsContainer.style.display = 'none';
  }

  if (elements.backButton) {
    elements.backButton.addEventListener('click', switchToCountryView);
  }

  if (elements.searchButton && elements.searchInput) {
    const handleSearch = async () => {
      const query = elements.searchInput.value.trim();
      if (!query) {
        return;
      }

      ui.showLoadingMessage();

      const normalizedQuery = query
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
      let targetBounds = null;
      let targetCenter = null;
      let isDepartmentSearchFlag = false;

      if (departmentBoundsMap.has(normalizedQuery)) {
        targetBounds = departmentBoundsMap.get(normalizedQuery);
        targetCenter = targetBounds.getCenter();
        isDepartmentSearchFlag = true;
      } else {
        const results = await api.geocodeAddress(query);
        if (results.length > 0) {
          const place = results[0];
          targetCenter = place.geometry.location;
          const viewport = place.geometry.viewport;
          targetBounds = L.latLngBounds(
            [viewport.southwest.lat, viewport.southwest.lng],
            [viewport.northeast.lat, viewport.northeast.lng]
          );
        }
      }

      if (targetBounds) {
        switchToRegionView(
          targetBounds,
          targetCenter,
          30,
          isDepartmentSearchFlag
        );
      } else {
        ui.showNoResultsMessage(query);
      }
    };
    elements.searchButton.addEventListener('click', handleSearch);
    elements.searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        handleSearch();
      }
    });
  }

  if (elements.geolocateButton) {
    elements.geolocateButton.addEventListener('click', () => {
      ui.showLoadingMessage('Recherche de votre position...');
      if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
          async (position) => {
            const {latitude: lat, longitude: lng} =
              position.coords;
            const center = {lat, lng};
            const radius = 50;
            const bounds = L.latLng(lat, lng).toBounds(
              radius * 1000
            );
            switchToRegionView(bounds, center, radius, false);
          },
          (error) => {
            let message = 'Une erreur inconnue est survenue.';
            switch (error.code) {
              case error.PERMISSION_DENIED:
                message =
                  "Géolocalisation refusée. Veuillez autoriser l'accès à votre position.";
                break;
              case error.POSITION_UNAVAILABLE:
                message =
                  'Informations de position non disponibles.';
                break;
              case error.TIMEOUT:
                message =
                  'La demande de géolocalisation a expiré.';
                break;
            }
            ui.showErrorMessage(message);
          }
        );
      } else {
        ui.showErrorMessage(
          'Votre navigateur ne supporte pas la géolocalisation.'
        );
      }
    });
  }

  async function initializeApp() {
    try {
      ui.showLoadingMessage('Chargement de la carte...');
      await mapController.loadGeoJson(geojsonUrl, onEachFeature);
      switchToCountryView();
    } catch (error) {
      console.error("Impossible d'initialiser la carte.", error);
      ui.showErrorMessage(
        'Une erreur est survenue lors du chargement de la carte.'
      );
    }
  }

  initializeApp();
  window.addEventListener('resize', () => mapController.invalidateSize());
});
