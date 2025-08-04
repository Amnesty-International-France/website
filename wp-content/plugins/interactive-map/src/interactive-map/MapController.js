import L from 'leaflet';

/**
 * Handle all the interactions with Leaflet Map Object.
 */
export class MapController {
  constructor(container, initialConfig) {
    this.map = L.map(container, {
      center: initialConfig.center,
      zoom: initialConfig.zoom || 5,
      zoomSnap: initialConfig.zoomSnap,
      zoomControl: false,
    });

    this.tileLayerUrl = initialConfig.tileLayerUrl;
    this.markerLayerGroup = L.layerGroup().addTo(this.map);
    this.geoJsonLayer = L.geoJSON();

    this.customMarkerIcon = L.divIcon({
      className: 'interactive-map__marker-icon',
      html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill-rule="evenodd" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/></svg>`,
      iconSize: [24, 32],
    });

    this.tileLayer = L.tileLayer(this.tileLayerUrl, {
      attribution: '&copy; OpenStreetMap contributors',
      subdomains: 'abc',
      maxZoom: 20,
    });

    L.control.zoom({position: 'topright'}).addTo(this.map);
  }

  /**
   * Loads and displays GeoJSON layer.
   * @param {string}   geoJsonUrl            - GeoJSON file URL.
   * @param {Function} onEachFeatureCallback - Function to execute for each department.
   */
  async loadGeoJson(geoJsonUrl, onEachFeatureCallback) {
    try {
      const response = await fetch(geoJsonUrl);
      if (!response.ok) {
        throw new Error(
          `Échec du chargement du GeoJSON depuis ${geoJsonUrl}`
        );
      }
      const data = await response.json();
      this.geoJsonLayer.addData(data);
      this.geoJsonLayer.eachLayer((layer) =>
        onEachFeatureCallback(layer.feature, layer)
      );
    } catch (error) {
      console.error(error);
      this.map.getContainer().innerHTML =
        '<p style="font-weight: bold;">Une erreur est survenue lors de l\'affichage de la carte.</p>';
      throw error;
    }
  }

  /**
   * Displays markers on the map.
   * @param {Array} markersData
   */
  displayMarkers(markersData) {
    this.markerLayerGroup.clearLayers();
    if (!markersData) {
      return;
    }

    markersData.forEach((markerInfo) => {
      const lat = parseFloat(
        markerInfo._geoloc?.lat ?? markerInfo.acf?.latitude
      );
      const lng = parseFloat(
        markerInfo._geoloc?.lng ?? markerInfo.acf?.longitude
      );

      if (!isNaN(lat) && !isNaN(lng)) {
        const popupContent = this.createPopupContent(markerInfo);
        L.marker([lat, lng], {icon: this.customMarkerIcon})
          .bindPopup(popupContent, {
            className: 'interactive-map__popup',
            offset: [0, -25],
          })
          .addTo(this.markerLayerGroup);
      }
    });
  }

  /**
   * Creates HTML content for the popup marker content.
   * @param {Object} markerInfo - Les informations du marqueur.
   * @return {string} Le HTML du popup.
   */
  createPopupContent(markerInfo) {
    const imageHtml = markerInfo.image
      ? `<div class="interactive-map__popup-image-wrapper"><img src="${markerInfo.image}" alt="" class="interactive-map__popup-image" loading="lazy"></div>`
      : '';
    return `
      <a href="${markerInfo.url || '#'}" class="interactive-map__popup-link">
        ${imageHtml}
        <div class="interactive-map__popup-content">
          <p class="interactive-map__popup-city">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/> </svg>
            <span class="interactive-map__popup-city-name">${markerInfo.city || ''}</span>
          </p>
          <h3 class="interactive-map__popup-title">${markerInfo.title || ''}</h3>
          <p class="interactive-map__popup-address">${markerInfo.address || ''}</p>
          <p class="interactive-map__popup-phone">${markerInfo.phone || ''}</p>
        </div>
      </a>`;
  }

  /**
   * Switches the map to "region" view with tiles.
   */
  switchToRegionView() {
    this.map.removeLayer(this.geoJsonLayer);
    this.map.addLayer(this.tileLayer);
    this.map.dragging.enable();
    this.map.scrollWheelZoom.enable();
    this.map.doubleClickZoom.enable();
    this.map.touchZoom.enable();
  }

  /**
   * Switches the map to "country" view with GeoJSON.
   * @param center
   * @param zoom
   */
  switchToCountryView(center, zoom) {
    this.markerLayerGroup.clearLayers();
    this.map.removeLayer(this.tileLayer);
    this.map.setView(center, zoom, {animate: false});
    this.map.addLayer(this.geoJsonLayer);
    this.map.dragging.disable();
    this.map.scrollWheelZoom.disable();
    this.map.doubleClickZoom.disable();
    this.map.touchZoom.disable();
  }

  /**
   * Fit bounds to markers.
   * @param {Array}          markersData
   * @param {L.LatLngBounds} fallbackBounds - Limits to use if there are no markers to display.
   */
  fitBoundsToMarkers(markersData, fallbackBounds = null) {
    const markerCoords = markersData
      .map((m) => [
        parseFloat(m._geoloc?.lat ?? m.acf?.latitude),
        parseFloat(m._geoloc?.lng ?? m.acf?.longitude),
      ])
      .filter(
        (coords) => !isNaN(coords[0]) && !isNaN(coords[1])
      );

    const options = {padding: [50, 50], animate: false};

    if (markerCoords.length > 0) {
      this.map.fitBounds(L.latLngBounds(markerCoords), options);
    } else if (fallbackBounds) {
      this.map.fitBounds(fallbackBounds, options);
    }
  }

  /**
   * Invalidate the map size to force a redrawing.
   */
  invalidateSize() {
    this.map.invalidateSize();
  }
}
