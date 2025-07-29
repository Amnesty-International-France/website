/**
 * Handles all DOM manipulation for the user interface,
 * except for the Leaflet map.
 */
export class UI {
  /**
   * @param {Object} elements - An object containing references to DOM elements.
   */
  constructor(elements) {
    this.elements = elements;
  }

  /**
   * Displays a list of markers data in card form.
   * @param {Array} markersData
   */
  displayMarkersAsCards(markersData) {
    this.clearResults();

    if (!markersData || markersData.length === 0) {
      this.showNoResultsMessage();
      return;
    }

    const cardsListContainer = document.createElement('div');
    cardsListContainer.classList.add('search-results-cards-list');

    markersData.forEach((markerInfo) => {
      const cardHtml = this.createCardHtml(markerInfo);
      const cardElement = document.createElement('div');
      cardElement.classList.add('search-result-card');
      cardElement.innerHTML = cardHtml;
      cardsListContainer.appendChild(cardElement);
    });

    this.elements.searchResultsDiv.appendChild(cardsListContainer);
  }

  /**
   * Creates the HTML content for a marker's card.
   * @param {Object} markerInfo
   * @return {string} The HTML of the card.
   */
  createCardHtml(markerInfo) {
    const cardLink = markerInfo.url || '#';
    const imageHtml = markerInfo.image
      ? `
      <div class="search-result-card__image-wrapper">
        <img src="${markerInfo.image}" alt="${markerInfo.title || 'Image'}" class="search-result-card__image" loading="lazy">
      </div>`
      : '';

    let addressHtml = '';
    if (markerInfo.city || markerInfo.address) {
      addressHtml = `
        <div class="card-address-wrapper">
          <span class="card-address-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z" fill="#575756"/>
            </svg>
          </span>
          <span class="card-address">${markerInfo.address || ''} ${markerInfo.city || ''}</span>
        </div>`;
    }

    return `
      <a href="${cardLink}" target="_blank" rel="noopener noreferrer" class="search-result-card__link">
        ${imageHtml}
        <div class="search-result-card__content">
          <h3 class="card-title">${markerInfo.title || 'Structure locale'}</h3>
            ${addressHtml}
        </div>
      </a>`;
  }

  /**
   * Displays a message in the result area.
   * @param {string} text
   * @param {string} className
   */
  showMessage(text, className) {
    this.clearResults();
    const messageElement = document.createElement('p');
    messageElement.classList.add('search-results-info', className);
    messageElement.textContent = text;
    this.elements.searchResultsDiv.appendChild(messageElement);
  }

  showInitialMessage() {
    this.showMessage(
      'Saisissez un lieu dans le moteur de recherche ou sélectionnez un département sur la carte.',
      'initial-message'
    );
  }

  showNoResultsMessage(query = '') {
    const message = query
      ? `Aucun résultat trouvé pour "${query}".`
      : 'Aucune structure trouvée dans cette zone.';
    this.showMessage(message, 'no-results');
  }

  showLoadingMessage(message = 'Recherche en cours...') {
    this.showMessage(message, 'loading');
  }

  showErrorMessage(message) {
    this.showMessage(message, 'error-message');
  }

  clearResults() {
    this.elements.searchResultsDiv.innerHTML = '';
  }

  /**
   * Updates the interface to switch to "region" or "country" view.
   * @param {boolean} isRegionView
   */
  toggleView(isRegionView) {
    this.elements.blockContainer.classList.toggle(
      'interactive-map--region-view',
      isRegionView
    );

    this.elements.mapContainer.style.overflow = isRegionView ? 'hidden' : 'visible';

    if (this.elements.backButton) {
      this.elements.backButton.style.opacity = isRegionView ? '1' : '0';
      this.elements.backButton.style.visibility = isRegionView
        ? 'visible'
        : 'hidden';
      this.elements.backButton.style.transform = isRegionView
        ? 'translateY(0)'
        : 'translateY(-20px)';
    }
    if (this.elements.selectorsContainer) {
      this.elements.selectorsContainer.style.display = isRegionView ? 'none' : '';
    }
  }

  /**
   * Updates the active vignette.
   * @param {HTMLElement|null} activeSelector - The vignette to activate.
   */
  updateActiveVignette(activeSelector) {
    const allSelectorParents =
      this.elements.blockContainer.querySelectorAll(
        '.interactive-map__selector'
      );
    allSelectorParents.forEach((s) =>
      s.classList.remove('interactive-map__selector--active')
    );
    if (activeSelector) {
      activeSelector.parentElement.classList.add(
        'interactive-map__selector--active'
      );
    }
  }
}
