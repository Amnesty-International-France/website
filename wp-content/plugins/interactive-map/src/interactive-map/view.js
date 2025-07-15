import L from "leaflet";
import "leaflet/dist/leaflet.css";

document.addEventListener("DOMContentLoaded", () => {
  const blockContainer = document.querySelector(".interactive-map");
  if (!blockContainer) {
    console.error(
      "Le conteneur du bloc interactif (.interactive-map) est introuvable.",
    );
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
  } = blockContainer.dataset;

  let mapCenter = [parseFloat(mapCenterLat), parseFloat(mapCenterLng)];
  let mapZoom = parseFloat(mapDefaultZoom);

  // --- Validations essentielles des URLs ---
  if (!geojsonUrl || !tileLayerUrl) {
    console.error(
      "Les URLs essentielles de la carte (geojsonUrl ou tileLayerUrl) sont manquantes.",
    );
    return;
  }

  const defaultSelector = blockContainer.querySelector(
    ".interactive-map__selector-thumbnail",
  );
  if (defaultSelector) {
    mapCenter = [
      parseFloat(defaultSelector.dataset.lat),
      parseFloat(defaultSelector.dataset.lng),
    ];
    mapZoom = parseFloat(defaultSelector.dataset.zoom);
  }

  const mapContainer = blockContainer.querySelector(
    ".interactive-map__container",
  );
  if (!mapContainer) {
    console.error(
      "Le conteneur de la carte (.interactive-map__container) est introuvable.",
    );
    return;
  }

  const backButton = blockContainer.querySelector(
    ".interactive-map__back-button",
  );
  const selectorsContainer = blockContainer.querySelector(
    ".interactive-map__selectors",
  );

  // Éléments du champ de recherche (maintenant à l'intérieur du bloc, sous interactive-map__wrapper)
  const searchInput = blockContainer.querySelector("#map-search-input");
  const searchButton = blockContainer.querySelector("#map-search-button");
  const searchResultsDiv = blockContainer.querySelector("#map-search-results");

  // --- Configuration de la carte Leaflet ---
  const markerLayerGroup = L.layerGroup();
  const customMarkerIcon = L.divIcon({
    className: "interactive-map__marker-icon",
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill-rule="evenodd" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/></svg>`,
    iconSize: [24, 32],
  });

  const tileLayer = L.tileLayer(
    `https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png`,
    {
      attribution: "&copy; OpenStreetMap contributors",
      subdomains: "abcd",
      maxZoom: 20,
    },
  );

  const geoJsonLayer = L.geoJSON(); // Pour les départements
  const map = L.map(mapContainer, {
    center: mapCenter,
    zoom: mapZoom,
    zoomControl: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    touchZoom: false,
    dragging: false,
  });
  L.control.zoom({ position: "topright" }).addTo(map);

  // --- Fonctions principales ---
  /**
   * Appelle l'API WordPress avec des limites géographiques pour obtenir les marqueurs.
   * @param {L.LatLngBounds} bounds - Limites géographiques.
   * @returns {Promise<Array>} - Retourne un tableau d'objets marqueurs.
   */
  const getMarkersForBounds = async (bounds) => {
    if (!apiEndpoint) {
      console.warn(
        "API endpoint non configuré pour la récupération des marqueurs.",
      );
      return [];
    }

    const url = new URL(apiEndpoint);
    url.searchParams.append("south", bounds.getSouth().toString());
    url.searchParams.append("west", bounds.getWest().toString());
    url.searchParams.append("north", bounds.getNorth().toString());
    url.searchParams.append("east", bounds.getEast().toString());

    console.log("Appel API pour les marqueurs:", url.toString()); // DEBUG

    try {
      const response = await fetch(url.toString(), {
        method: "GET",
        headers: { "Content-Type": "application/json" },
      });
      if (!response.ok) {
        console.error(`Erreur API: ${response.status} ${response.statusText}`);
        return [];
      }
      const data = await response.json();
      console.log("Réponse API pour les marqueurs:", data); // DEBUG
      return data;
    } catch (error) {
      console.error("Failed to fetch markers from API:", error);
      return [];
    }
  };

  /**
   * Affiche les marqueurs sur la carte et ajuste la vue.
   * @param {Array} markersData - Tableau d'objets marqueurs.
   * @param {L.LatLngBounds} bounds - Limites géographiques pour ajuster la vue. Peut être null si on veut juste fitter sur les marqueurs.
   */
  const displayMarkers = (markersData, bounds) => {
    markerLayerGroup.clearLayers(); // Efface les marqueurs précédents

    if (markersData && markersData.length > 0) {
      markersData.forEach((markerInfo) => {
        // La structure de données de l'API PHP que je vous ai fournie utilise `_geoloc`
        const lat = parseFloat(markerInfo._geoloc.lat);
        const lng = parseFloat(markerInfo._geoloc.lng);

        if (!isNaN(lat) && !isNaN(lng)) {
          const latLng = [lat, lng];
          const popupContent = `
                        <a href="${
                          markerInfo.url || "#"
                        }" class="interactive-map__popup-link">
                            <div class="interactive-map__popup-image-wrapper">
                                <img src="${
                                  markerInfo.image || ""
                                }" alt="" class="interactive-map__popup-image" style="display: ${
                                  markerInfo.image ? "block" : "none"
                                };" loading="lazy">
                            </div>
                            <div class="interactive-map__popup-content">
                                <h3 class="interactive-map__popup-title">${
                                  markerInfo.title || ""
                                }</h3>
                                <p class="interactive-map__popup-address">${
                                  markerInfo.address || ""
                                }</p>
                                <p class="interactive-map__popup-phone">${
                                  markerInfo.phone || ""
                                }</p>
                                <p class="interactive-map__popup-email">${
                                  markerInfo.email || ""
                                }</p>
                            </div>
                        </a>`;
          L.marker(latLng, { icon: customMarkerIcon })
            .bindPopup(popupContent, {
              className: "interactive-map__popup",
              offset: [0, -25],
            })
            .addTo(markerLayerGroup);
        } else {
          console.warn(
            "Marqueur avec coordonnées invalides, ignoré:",
            markerInfo,
          );
        }
      });
      if (searchResultsDiv)
        searchResultsDiv.textContent += ` (${markersData.length} structures trouvées)`;
    } else {
      if (searchResultsDiv)
        searchResultsDiv.textContent += ` (0 structures trouvées)`;
      console.log("Aucune structure locale trouvée dans les limites données.");
    }

    map.addLayer(markerLayerGroup);

    if (bounds) {
      map.fitBounds(bounds, { padding: [50, 50] });
    } else if (markersData.length > 0) {
      const allMarkerCoords = markersData
        .filter((m) => m._geoloc && !isNaN(parseFloat(m._geoloc.lat)))
        .map((m) => [parseFloat(m._geoloc.lat), parseFloat(m._geoloc.lng)]);
      if (allMarkerCoords.length > 0) {
        map.fitBounds(L.latLngBounds(allMarkerCoords), { padding: [50, 50] });
      }
    }
    map.dragging.enable();
    map.scrollWheelZoom.enable();
    map.doubleClickZoom.enable();
  };

  /**
   * Bascule vers la vue régionale/locale, masque les départements et affiche les marqueurs.
   * @param {L.LatLngBounds} bounds - Les limites géographiques de la zone à afficher.
   */
  const switchToRegionView = async (bounds) => {
    blockContainer.classList.add("interactive-map--region-view");
    map.removeLayer(geoJsonLayer);
    map.addLayer(tileLayer);

    if (backButton) {
      backButton.style.opacity = 1;
      backButton.style.visibility = "visible";
      backButton.style.transform = "translateY(0)";
    }
    if (selectorsContainer) {
      selectorsContainer.style.display = "none"; // Masque les vignettes
    }

    const regionMarkers = await getMarkersForBounds(bounds);
    displayMarkers(regionMarkers, bounds);
  };

  /**
   * Bascule vers la vue pays, affiche les départements et masque les marqueurs.
   */
  const switchToCountryView = () => {
    blockContainer.classList.remove("interactive-map--region-view");
    markerLayerGroup.clearLayers(); // Efface tous les marqueurs
    map.removeLayer(tileLayer); // Masque la couche de tuiles détaillée

    map.setView(mapCenter, mapZoom, { animate: false }); // Retourne à la vue initiale du pays
    map.addLayer(geoJsonLayer); // Réaffiche la couche des départements

    map.dragging.disable();
    map.scrollWheelZoom.disable();
    map.doubleClickZoom.disable();

    // Visibilité des contrôles
    if ("true" === showVignettes && selectorsContainer) {
      selectorsContainer.style.display = ""; // Rétablit l'affichage des vignettes
    }
    if (backButton) {
      backButton.style.opacity = 0;
      backButton.style.visibility = "hidden";
      backButton.style.transform = "translateY(-20px)";
    }
    if (searchResultsDiv) {
      searchResultsDiv.textContent = ""; // Efface les anciens résultats de recherche
      if (searchInput) searchInput.value = ""; // Efface le champ de recherche
    }
  };

  /**
   * Configure les interactions pour chaque département sur la carte GeoJSON.
   */
  const onEachFeature = (feature, layer) => {
    const departmentName = feature.properties.nom || "N/A";
    const departmentCode = feature.properties.code || "";
    layer.bindTooltip(`${departmentName.toUpperCase()} (${departmentCode})`, {
      className: "interactive-map__department-tooltip",
      permanent: false,
      direction: "auto",
      opacity: 1,
    });

    layer.on({
      mouseover: (e) =>
        e.target.getElement().classList.add("leaflet-interactive--highlighted"),
      mouseout: (e) =>
        e.target
          .getElement()
          .classList.remove("leaflet-interactive--highlighted"),
      click: (e) => {
        const bounds = e.target.getBounds();
        switchToRegionView(bounds); // Déclenche la vue région avec les limites du département cliqué
      },
    });
  };

  // --- Chargement initial du GeoJSON des départements ---
  fetch(geojsonUrl)
    .then((response) => {
      if (!response.ok)
        throw new Error(`Échec du chargement du GeoJSON depuis ${geojsonUrl}`);
      return response.json();
    })
    .then((data) => {
      geoJsonLayer.addData(data);
      geoJsonLayer.eachLayer((layer) => onEachFeature(layer.feature, layer));
      map.addLayer(geoJsonLayer);
      map.invalidateSize(); // S'assure que la carte s'affiche correctement
    })
    .catch((error) => {
      console.error(error);
      mapContainer.innerHTML =
        '<p style="font-weight: bold;">Une erreur est survenue lors de l\'affichage de la carte des départements.</p>';
    });

  // --- Gestion des vignettes (si activées) ---
  if ("true" === showVignettes && selectorsContainer) {
    const selectors = blockContainer.querySelectorAll(
      ".interactive-map__selector-thumbnail",
    );
    selectors.forEach((selector, index) => {
      selector.addEventListener("click", (e) => {
        const currentSelector = e.currentTarget;

        // Logique de classe active pour les vignettes
        const allSelectorParents = blockContainer.querySelectorAll(
          ".interactive-map__selector",
        );
        allSelectorParents.forEach((s) =>
          s.classList.remove("interactive-map__selector--active"),
        );
        currentSelector.parentElement.classList.add(
          "interactive-map__selector--active",
        );

        if (index === 0) {
          // Si c'est la vignette "France" ou "Retour au pays"
          switchToCountryView();
          return;
        }

        const lat = parseFloat(currentSelector.dataset.lat);
        const lng = parseFloat(currentSelector.dataset.lng);
        const zoom = parseFloat(currentSelector.dataset.zoom);

        if (!isNaN(lat) && !isNaN(lng) && !isNaN(zoom)) {
          // Zoom d'abord, puis récupération des limites pour l'API
          map.setView([lat, lng], zoom, { animate: false });
          const bounds = map.getBounds(); // Obtient les limites après le setView
          switchToRegionView(bounds); // Déclenche la vue région avec ces limites
        }
      });
    });
  } else if (selectorsContainer) {
    selectorsContainer.style.display = "none"; // Masque les vignettes si non activées
  }

  // --- Bouton de retour ---
  backButton.addEventListener("click", () => {
    switchToCountryView(); // Retourne à la vue pays
    // Réinitialise la classe active pour la vignette "France"
    const allSelectorParents = blockContainer.querySelectorAll(
      ".interactive-map__selector",
    );
    allSelectorParents.forEach((s) =>
      s.classList.remove("interactive-map__selector--active"),
    );
    if (allSelectorParents.length > 0) {
      allSelectorParents[0].classList.add("interactive-map__selector--active");
    }
  });

  // --- Fonctionnalité de recherche via le proxy Google Maps Geocoding ---
  if (geocodeProxyEndpoint && searchInput && searchButton) {
    searchButton.addEventListener("click", async () => {
      const query = searchInput.value.trim();
      if (!query) {
        if (searchResultsDiv)
          searchResultsDiv.textContent =
            "Veuillez entrer une adresse ou un lieu.";
        return;
      }

      if (searchResultsDiv)
        searchResultsDiv.textContent = "Recherche en cours...";

      try {
        // Appel à votre endpoint proxy WordPress
        const proxyUrl = new URL(geocodeProxyEndpoint);
        proxyUrl.searchParams.append("address", query);

        console.log("Appel au proxy Geocoding WP:", proxyUrl.toString()); // DEBUG
        const response = await fetch(proxyUrl.toString());
        const data = await response.json();
        console.log("Réponse du proxy Geocoding WP:", data); // DEBUG

        if (data.status === "OK" && data.results.length > 0) {
          const place = data.results[0];
          const bounds = place.geometry.viewport; // Les limites de la zone du lieu

          if (searchResultsDiv)
            searchResultsDiv.textContent = `Lieu trouvé: ${place.formatted_address}`;

          // Convertir les limites Google Maps en L.LatLngBounds pour Leaflet
          const leafletBounds = L.latLngBounds(
            [bounds.southwest.lat, bounds.southwest.lng],
            [bounds.northeast.lat, bounds.northeast.lng],
          );

          console.log("Limites Leaflet pour la recherche:", leafletBounds); // DEBUG

          // Déclenche la vue région avec les limites du lieu recherché
          switchToRegionView(leafletBounds);
        } else {
          if (searchResultsDiv)
            searchResultsDiv.textContent =
              "Aucun résultat trouvé pour cette recherche.";
          switchToCountryView(); // Retour à la vue pays si pas de résultat
        }
      } catch (error) {
        console.error(
          "Erreur lors de la recherche géographique via proxy:",
          error,
        );
        if (searchResultsDiv)
          searchResultsDiv.textContent =
            "Une erreur est survenue lors de la recherche.";
        switchToCountryView();
      }
    });
  }

  // --- État initial : La carte commence en vue pays ---
  map.dragging.disable();
  map.scrollWheelZoom.disable();
});
