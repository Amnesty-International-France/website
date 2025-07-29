/**
 * Manages API requests
 */
export class Api {
  /**
   * @param {string} apiEndpoint          - Markers URL endpoint.
   * @param {string} geocodeProxyEndpoint - The proxy endpoint URL for geocoding.
   */
  constructor(apiEndpoint, geocodeProxyEndpoint) {
    this.apiEndpoint = apiEndpoint;
    this.geocodeProxyEndpoint = geocodeProxyEndpoint;
  }

  /**
   * Retrieves markers for given geographic boundaries.
   * @param {L.LatLngBounds} bounds       - Geographical limits.
   * @param {object|null}    center       - The center of research (lat, lng).
   * @param {number|null}    radius       - The search radius in km.
   * @param {boolean}        isDepartment - Indicates whether the search concerns a department.
   * @return {Promise<Array>}
   */
  async getMarkersForBounds(
    bounds,
    center = null,
    radius = null,
    isDepartment = false
  ) {
    if (!this.apiEndpoint) {
      console.warn(
        'API endpoint non configuré pour la récupération des marqueurs.'
      );
      return [];
    }

    const url = new URL(this.apiEndpoint);
    url.searchParams.append('south', bounds.getSouth().toString());
    url.searchParams.append('west', bounds.getWest().toString());
    url.searchParams.append('north', bounds.getNorth().toString());
    url.searchParams.append('east', bounds.getEast().toString());

    if (center && radius) {
      url.searchParams.append('center_lat', center.lat.toString());
      url.searchParams.append('center_lng', center.lng.toString());
      url.searchParams.append('radius', radius.toString());
    }
    if (isDepartment) {
      url.searchParams.append('is_department_search', 'true');
    }

    try {
      const response = await fetch(url.toString(), {
        method: 'GET',
        headers: {'Content-Type': 'application/json'},
      });
      if (!response.ok) {
        console.error(
          `Erreur API: ${response.status} ${response.statusText}`
        );
        return [];
      }
      return await response.json();
    } catch (error) {
      console.error(
        "Échec de la récupération des marqueurs depuis l'API:",
        error
      );
      return [];
    }
  }

  /**
   * Geocode an address using a proxy endpoint.
   * @param {string} address - The address to geocode.
   * @return {Promise<Array>}
   */
  async geocodeAddress(address) {
    if (!this.geocodeProxyEndpoint) {
      console.error(
        "L'endpoint proxy de géocodage n'est pas configuré."
      );
      return [];
    }
    const proxyUrl = new URL(this.geocodeProxyEndpoint);
    proxyUrl.searchParams.append('address', address);

    try {
      const response = await fetch(proxyUrl.toString());
      const data = await response.json();

      if (data.status === 'OK' && data.results.length > 0) {
        return data.results;
      }
      console.warn(
        'Statut de géocodage:',
        data.status,
        data.error_message
      );
      return [];
    } catch (error) {
      console.error(
        'Erreur lors de la recherche géographique via proxy:',
        error
      );
      return [];
    }
  }
}
