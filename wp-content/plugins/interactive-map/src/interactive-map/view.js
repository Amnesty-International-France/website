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

  const searchForm = blockContainer.querySelector(
    ".interactive-map__search-form",
  );
  const searchInput = blockContainer.querySelector("#map-search-input");
  const searchButton = blockContainer.querySelector("#map-search-button");
  const searchResultsDiv = blockContainer.querySelector("#map-search-results");
  const geolocateButton = blockContainer.querySelector(".geolocate-me");

  const markerLayerGroup = L.layerGroup();
  const customMarkerIcon = L.divIcon({
    className: "interactive-map__marker-icon",
    html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/> </svg>`,
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

  const geoJsonLayer = L.geoJSON();
  const map = L.map(mapContainer, {
    center: mapCenter,
    zoom: mapZoom,
    zoomControl: false,
  });
  L.control.zoom({ position: "topright" }).addTo(map);

  const departmentBoundsMap = new Map();

  const getMarkersForBounds = async (
    bounds,
    center = null,
    radius = null,
    isDepartment = false,
  ) => {
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

    if (center && radius) {
      url.searchParams.append("center_lat", center.lat.toString());
      url.searchParams.append("center_lng", center.lng.toString());
      url.searchParams.append("radius", radius.toString());
    }
    if (isDepartment) {
      url.searchParams.append("is_department_search", "true");
    }

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
      return data;
    } catch (error) {
      console.error(
        "Échec de la récupération des marqueurs depuis l'API:",
        error,
      );
      return [];
    }
  };

  const displayMarkers = (markersData, boundsToFit = null) => {
    markerLayerGroup.clearLayers();
    searchResultsDiv.innerHTML = "";

    const cardsListContainer = document.createElement("div");
    cardsListContainer.classList.add("search-results-cards-list");

    if (markersData && markersData.length > 0) {
      markersData.forEach((markerInfo) => {
        const lat = parseFloat(
          markerInfo._geoloc
            ? markerInfo._geoloc.lat
            : markerInfo.acf?.latitude,
        );
        const lng = parseFloat(
          markerInfo._geoloc
            ? markerInfo._geoloc.lng
            : markerInfo.acf?.longitude,
        );

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
                            <p class="interactive-map__popup-city">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" ><path fill="currentColor" d="M192 512C86 330 0 269.4 0 192 0 85.96 85.96 0 192 0s192 85.96 192 192c0 77.4-86 138-192 320zM192 272a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" clip-rule="evenodd"/> </svg>
                                <span class="interactive-map__popup-city-name">${
                                  markerInfo.city || ""
                                }</span>
                            </p>
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
          const leafletMarker = L.marker(latLng, { icon: customMarkerIcon })
            .bindPopup(popupContent, {
              className: "interactive-map__popup",
              offset: [0, -25],
            })
            .addTo(markerLayerGroup);

          const cardLink = markerInfo.url || "#";

          let cardImageHtml = "";
          if (markerInfo.image) {
            cardImageHtml = `
              <div class="search-result-card__image-wrapper">
                <img src="${markerInfo.image}" alt="${
                  markerInfo.title || "Image"
                }" class="search-result-card__image" loading="lazy">
              </div>`;
          }

          let cardTextContent = `
            <div class="search-result-card__content">
              <h3 class="card-title">${
                markerInfo.title || "Structure locale"
              }</h3>`;

          if (markerInfo.city || markerInfo.address) {
            cardTextContent += `
                  <div class="card-address-wrapper">
                    <span class="card-address-icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z" fill="#575756"/>
                      </svg>
                    </span>
                    <span class="card-address">${markerInfo.address || ""} ${
                      markerInfo.city || ""
                    }</span>
                  </div>`;
          }
          cardTextContent += `</div>`;

          const cardHtml = `
            <a href="${cardLink}" target="_blank" rel="noopener noreferrer" class="search-result-card__link">
                ${cardImageHtml}
                ${cardTextContent}
            </a>`;

          const card = document.createElement("div");
          card.classList.add("search-result-card");
          card.innerHTML = cardHtml;

          cardsListContainer.appendChild(card);
        } else {
          console.warn(
            "Marqueur avec coordonnées invalides, ignoré:",
            markerInfo,
          );
        }
      });
      searchResultsDiv.appendChild(cardsListContainer);
    } else {
      const noResultsItem = document.createElement("p");
      noResultsItem.classList.add("search-results-info", "no-results");
      noResultsItem.textContent = `Aucune structure trouvée dans cette zone.`;
      searchResultsDiv.appendChild(noResultsItem);
    }

    map.addLayer(markerLayerGroup);

    if (markersData.length > 0) {
      const allMarkerCoords = markersData
        .filter(
          (m) =>
            (m._geoloc && !isNaN(parseFloat(m._geoloc.lat))) ||
            (m.acf?.latitude && !isNaN(parseFloat(m.acf.latitude))),
        )
        .map((m) => [
          parseFloat(m._geoloc ? m._geoloc.lat : m.acf.latitude),
          parseFloat(m._geoloc ? m._geoloc.lng : m.acf.longitude),
        ]);
      if (allMarkerCoords.length > 0) {
        map.fitBounds(L.latLngBounds(allMarkerCoords), { padding: [50, 50] });
      }
    } else if (boundsToFit) {
      map.fitBounds(boundsToFit, { padding: [50, 50] });
    }

    map.dragging.enable();
    map.scrollWheelZoom.enable();
    map.doubleClickZoom.enable();
    map.touchZoom.enable();

    map.invalidateSize();
  };

  const switchToRegionView = async (
    bounds,
    center = null,
    radius = null,
    isDepartment = false,
  ) => {
    blockContainer.classList.add("interactive-map--region-view");
    map.removeLayer(geoJsonLayer);
    map.addLayer(tileLayer);

    if (backButton) {
      backButton.style.opacity = 1;
      backButton.style.visibility = "visible";
      backButton.style.transform = "translateY(0)";
    }
    if (selectorsContainer) {
      selectorsContainer.style.display = "none";
    }

    if (searchInput) searchInput.value = "";

    const regionMarkers = await getMarkersForBounds(
      bounds,
      center,
      radius,
      isDepartment,
    );
    displayMarkers(regionMarkers, null);
  };

  const switchToCountryView = () => {
    blockContainer.classList.remove("interactive-map--region-view");
    markerLayerGroup.clearLayers();
    map.removeLayer(tileLayer);

    map.setView(mapCenter, mapZoom, { animate: false });
    map.addLayer(geoJsonLayer);

    map.dragging.disable();
    map.scrollWheelZoom.disable();
    map.doubleClickZoom.enable();
    map.touchZoom.enable();

    map.invalidateSize();

    if ("true" === showVignettes && selectorsContainer) {
      selectorsContainer.style.display = "";
    }
    if (backButton) {
      backButton.style.opacity = 0;
      backButton.style.visibility = "hidden";
      backButton.style.transform = "translateY(-20px)";
    }
    if (searchInput) searchInput.value = "";
    searchResultsDiv.innerHTML = "";

    const initialMessage = document.createElement("p");
    initialMessage.classList.add("search-results-info", "initial-message");
    initialMessage.textContent =
      "Saisissez un lieu dans le moteur de recherche ou sélectionnez un département sur la carte.";
    searchResultsDiv.appendChild(initialMessage);
  };

  const onEachFeature = (feature, layer) => {
    const departmentName = feature.properties.nom || "N/A";
    const departmentCode = feature.properties.code_insee || "";

    const normalizedGeoJsonName = departmentName
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");

    departmentBoundsMap.set(normalizedGeoJsonName, layer.getBounds());
    departmentBoundsMap.set(departmentCode.toLowerCase(), layer.getBounds());

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
        switchToRegionView(bounds);
      },
    });
  };

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
      map.invalidateSize();
      switchToCountryView();
    })
    .catch((error) => {
      console.error(error);
      mapContainer.innerHTML =
        '<p style="font-weight: bold;">Une erreur est survenue lors de l\'affichage de la carte des départements.</p>';
    });

  if ("true" === showVignettes && selectorsContainer) {
    const selectors = blockContainer.querySelectorAll(
      ".interactive-map__selector-thumbnail",
    );
    selectors.forEach((selector, index) => {
      selector.addEventListener("click", (e) => {
        const currentSelector = e.currentTarget;

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
          switchToCountryView();
          return;
        }

        const lat = parseFloat(currentSelector.dataset.lat);
        const lng = parseFloat(currentSelector.dataset.lng);
        const zoom = parseFloat(currentSelector.dataset.zoom);

        if (!isNaN(lat) && !isNaN(lng) && !isNaN(zoom)) {
          map.setView([lat, lng], zoom, { animate: false });
          const bounds = map.getBounds();
          switchToRegionView(bounds);
        }
      });
    });
  } else if (selectorsContainer) {
    selectorsContainer.style.display = "none";
  }

  if (backButton) {
    backButton.addEventListener("click", () => {
      switchToCountryView();
      const allSelectorParents = blockContainer.querySelectorAll(
        ".interactive-map__selector",
      );
      allSelectorParents.forEach((s) =>
        s.classList.remove("interactive-map__selector--active"),
      );
      if (allSelectorParents.length > 0) {
        allSelectorParents[0].classList.add(
          "interactive-map__selector--active",
        );
      }
    });
  }

  const geocodeAddress = async (address) => {
    if (!geocodeProxyEndpoint) {
      console.error("L'endpoint proxy de géocodage n'est pas configuré.");
      return [];
    }
    const proxyUrl = new URL(geocodeProxyEndpoint);
    proxyUrl.searchParams.append("address", address);

    try {
      const response = await fetch(proxyUrl.toString());
      const data = await response.json();

      if (data.status === "OK" && data.results.length > 0) {
        return data.results;
      } else {
        console.warn("Statut de géocodage:", data.status, data.error_message);
        return [];
      }
    } catch (error) {
      console.error(
        "Erreur lors de la recherche géographique via proxy:",
        error,
      );
      return [];
    }
  };

  let searchTimeout;

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      clearTimeout(searchTimeout);
      const query = searchInput.value.trim();
      if (query.length < 3) {
        searchResultsDiv.innerHTML = "";
        return;
      }
    });
  }

  if (searchButton && searchInput) {
    searchButton.addEventListener("click", async () => {
      const query = searchInput.value.trim();
      if (!query) {
        searchResultsDiv.innerHTML = "";
        return;
      }

      searchResultsDiv.innerHTML = "";

      const normalizedQuery = query
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
      let targetBounds = null;
      let targetCenter = null;
      let isDepartmentSearchFlag = false;

      if (departmentBoundsMap.has(normalizedQuery)) {
        targetBounds = departmentBoundsMap.get(normalizedQuery);
        targetCenter = targetBounds.getCenter();
        isDepartmentSearchFlag = true;
      } else {
        try {
          const results = await geocodeAddress(query);

          if (results.length > 0) {
            const place = results[0];
            targetCenter = place.geometry.location;

            let south = place.geometry.viewport.southwest.lat;
            let west = place.geometry.viewport.southwest.lng;
            let north = place.geometry.viewport.northeast.lat;
            let east = place.geometry.viewport.northeast.lng;

            const MIN_DEGREE_SPAN = 1.5;

            if (
              north - south < MIN_DEGREE_SPAN ||
              east - west < MIN_DEGREE_SPAN
            ) {
              const searchRadiusKm = 30;

              const approxDegreeDeltaLat = searchRadiusKm / 111.0;
              const approxDegreeDeltaLng =
                searchRadiusKm /
                (111.0 * Math.cos((targetCenter.lat * Math.PI) / 180));

              south = Math.min(south, targetCenter.lat - approxDegreeDeltaLat);
              north = Math.max(north, targetCenter.lat + approxDegreeDeltaLat);
              west = Math.min(west, targetCenter.lng - approxDegreeDeltaLng);
              east = Math.max(east, targetCenter.lng + approxDegreeDeltaLng);
            }
            targetBounds = L.latLngBounds([south, west], [north, east]);
            isDepartmentSearchFlag = false;
          } else {
            searchResultsDiv.innerHTML = "";
            const noResultsItem = document.createElement("p");
            noResultsItem.classList.add("search-results-info", "no-results");
            noResultsItem.textContent = `Aucun résultat trouvé pour "${query}".`;
            searchResultsDiv.appendChild(noResultsItem);
            switchToCountryView();
            return;
          }
        } catch (error) {
          console.error("Erreur lors de la recherche géographique:", error);
          searchResultsDiv.innerHTML = "";
          const errorMessage = document.createElement("p");
          errorMessage.classList.add("search-results-info", "no-results");
          errorMessage.textContent =
            "Une erreur est survenue lors de la recherche.";
          searchResultsDiv.appendChild(errorMessage);
          switchToCountryView();
          return;
        }
      }

      if (targetBounds && targetCenter) {
        const searchRadiusKm = 30;

        switchToRegionView(
          targetBounds,
          targetCenter,
          searchRadiusKm,
          isDepartmentSearchFlag,
        );
        searchInput.value = query;
      } else {
        searchResultsDiv.innerHTML = "";
        const noResultsItem = document.createElement("p");
        noResultsItem.classList.add("search-results-info", "no-results");
        noResultsItem.textContent = `Aucun résultat trouvé pour "${query}".`;
        searchResultsDiv.appendChild(noResultsItem);
        switchToCountryView();
      }
    });
  }

  if (geolocateButton) {
    geolocateButton.addEventListener("click", () => {
      searchResultsDiv.innerHTML = "";
      const loadingMessage = document.createElement("p");
      loadingMessage.classList.add("search-results-info", "loading");
      loadingMessage.textContent = "Recherche de votre position...";
      searchResultsDiv.appendChild(loadingMessage);

      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
          async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const geolocatedCenter = L.latLng(lat, lng);
            const geolocationRadiusKm = 50;

            const approximateBounds = L.latLngBounds(
              geolocatedCenter.toBounds(geolocationRadiusKm * 1000),
            );

            const localStructures = await getMarkersForBounds(
              approximateBounds,
              { lat: lat, lng: lng },
              geolocationRadiusKm,
              false,
            );

            if (localStructures.length > 0) {
              switchToRegionView(
                L.latLngBounds(
                  localStructures.map((m) => [m._geoloc.lat, m._geoloc.lng]),
                ),
                { lat: lat, lng: lng },
                geolocationRadiusKm,
                false,
              );
            } else {
              searchResultsDiv.innerHTML = "";
              const noResultsItem = document.createElement("p");
              noResultsItem.classList.add("search-results-info", "no-results");
              noResultsItem.textContent = `Aucune structure trouvée à proximité de votre position actuelle (dans un rayon de ${geolocationRadiusKm} km).`;
              searchResultsDiv.appendChild(noResultsItem);
              map.setView(geolocatedCenter, 10);
            }
          },
          (error) => {
            searchResultsDiv.innerHTML = "";
            const errorMessage = document.createElement("p");
            errorMessage.classList.add("search-results-info", "no-results");
            switch (error.code) {
              case error.PERMISSION_DENIED:
                errorMessage.textContent =
                  "Géolocalisation refusée. Veuillez autoriser l'accès à votre position pour utiliser cette fonctionnalité.";
                break;
              case error.POSITION_UNAVAILABLE:
                errorMessage.textContent =
                  "Informations de position non disponibles. Veuillez réessayer.";
                break;
              case error.TIMEOUT:
                errorMessage.textContent =
                  "La demande de géolocalisation a expiré. Veuillez réessayer.";
                break;
              case error.UNKNOWN_ERROR:
                errorMessage.textContent =
                  "Une erreur inconnue est survenue lors de la géolocalisation.";
                break;
            }
            searchResultsDiv.appendChild(errorMessage);
          },
          {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0,
          },
        );
      } else {
        searchResultsDiv.innerHTML = "";
        const notSupportedMessage = document.createElement("p");
        notSupportedMessage.classList.add("search-results-info", "no-results");
        notSupportedMessage.textContent =
          "Votre navigateur ne supporte pas la géolocalisation.";
        searchResultsDiv.appendChild(notSupportedMessage);
      }
    });
  }

  document.addEventListener("click", (e) => {
    const searchContainer = blockContainer.querySelector(
      ".interactive-map__search-container",
    );
    if (searchContainer && !searchContainer.contains(e.target)) {
      if (
        searchResultsDiv.childElementCount > 0 &&
        !searchResultsDiv.querySelector(".search-results-cards-list")
      ) {
        searchResultsDiv.innerHTML = "";
      } else if (searchResultsDiv.childElementCount === 0) {
        const initialMessage = document.createElement("p");
        initialMessage.classList.add("search-results-info", "initial-message");
        initialMessage.textContent =
          "Saisissez un lieu dans le moteur de recherche ou sélectionnez un département sur la carte.";
        searchResultsDiv.appendChild(initialMessage);
      }
    }
  });

  window.addEventListener("resize", () => {
    map.invalidateSize();
  });
});
