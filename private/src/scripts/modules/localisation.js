const defaultApiGouvUrl = 'https://geo.api.gouv.fr';

const checkSizeNumber = (sizeMin, sizeMax, value) => {
  let regex = new RegExp(`^[0-9]{${sizeMin}}$`);

  if (sizeMax && sizeMax > sizeMin) {
    regex = new RegExp(`^[0-9]{${sizeMin},${sizeMax}}$`);
  }
  if (sizeMin === 0 && sizeMax === 0) {
    regex = /^[0-9]+$/;
  }

  return regex.test(value);
};

// eslint-disable-next-line consistent-return
const fetchZipCodeFromLaPosteApi = async (city) => {
  try {
    const apiLaPoste =
      'https://datanova.laposte.fr/data-fair/api/v1/datasets/laposte-hexasmal/lines';

    const queryForLaPosteApi = new URLSearchParams({
      select: 'code_postal',
      q: city,
      size: 1,
    });

    const getAssociatedCitiesZipCode = await fetch(`${apiLaPoste}?${queryForLaPosteApi}`);
    return getAssociatedCitiesZipCode.json();
  } catch (err) {
    console.error(`error fetch: ${err.code} - ${err.message}`);
  }
};

const fetchCitiesFromDep = async (codeDep, query) => {
  const queryString = new URLSearchParams(query).toString();
  const apiGeoDep = `${defaultApiGouvUrl}/departements`;
  const responseFromDep = await fetch(`${apiGeoDep}/${codeDep}/communes?${queryString}`);

  return responseFromDep.json();
};

const fetchCitiesFromQuery = async (query) => {
  const apiGeoCities = `${defaultApiGouvUrl}/communes`;
  const queryString = new URLSearchParams(query).toString();
  const response = await fetch(`${apiGeoCities}?${queryString}`);

  return response.json();
};

const fetchAssociatedCities = async (query) => {
  const queryString = new URLSearchParams(query).toString();
  const apiAssociatedCities = `${defaultApiGouvUrl}/communes_associees_deleguees`;
  const responseFromAssociatedCities = await fetch(`${apiAssociatedCities}?${queryString}`);

  return responseFromAssociatedCities.json();
};

// eslint-disable-next-line consistent-return
const fetchApiGeoFrance = async (userLocation) => {
  const userLocationWithTwoDigits = userLocation.match(/^(\d{2})/)?.[1];
  const unvalidCodeDep = ['20', '96', '97', '98', '99'];

  if (!userLocation) {
    return [];
  }

  const globalQuery = {
    fields: 'codesPostaux,nom,centre',
  };

  let query = { ...globalQuery };
  const userLocationIsNum = checkSizeNumber(0, 0, userLocation);
  const userLocationIsCP = checkSizeNumber(5, 0, userLocation);
  const corsicaDep = ['2A', '2B'];
  const hybridCodeDep = corsicaDep.includes(userLocation);
  const departmentWithThreeDigits = [
    '075',
    '971',
    '972',
    '973',
    '974',
    '975',
    '978',
    '986',
    '987',
    '988',
  ];
  try {
    if (userLocationIsNum && !userLocation.includes('20')) {
      if (!userLocationWithTwoDigits || unvalidCodeDep.includes(userLocation)) return [];
      let codeDep;

      if (userLocationWithTwoDigits) {
        codeDep = userLocation;
      }

      if (checkSizeNumber(3, 0, userLocation) && departmentWithThreeDigits.includes(userLocation)) {
        codeDep = userLocation;
      }

      if (
        checkSizeNumber(2, 4, userLocation) &&
        !departmentWithThreeDigits.includes(userLocation)
      ) {
        codeDep = userLocationWithTwoDigits;
      }

      if (checkSizeNumber(2, 4, userLocation)) {
        query = {
          format: 'json',
          geometry: 'centre',
          ...globalQuery,
        };

        const dataFromDep = await fetchCitiesFromDep(codeDep, query);

        if (checkSizeNumber(2, 0, userLocation)) {
          return dataFromDep.slice(0, 10);
        }

        if (checkSizeNumber(3, 4, userLocation)) {
          return dataFromDep.filter((city) =>
            city.codesPostaux.some((item) => item.startsWith(userLocation)),
          );
        }
      }
    }

    if (userLocationIsCP) {
      query = {
        codePostal: userLocation,
        ...globalQuery,
      };

      const dataFromCities = await fetchCitiesFromQuery(query);

      return [...dataFromCities];
    }

    if (hybridCodeDep || userLocation.includes('20')) {
      query = {
        format: 'json',
        geometry: 'centre',
        limit: 10,
        ...globalQuery,
      };

      const corsicaResults = [];

      if (userLocation.includes('20')) {
        await Promise.all(
          corsicaDep.map(async (dep) => {
            const data = await fetchCitiesFromDep(dep, query);
            corsicaResults.push(...data);
          }),
        );
      }

      if (checkSizeNumber(3, 4, userLocation)) {
        return corsicaResults.filter((city) =>
          city.codesPostaux.some((cp) => cp.startsWith(userLocation)),
        );
      }

      if (hybridCodeDep && corsicaDep.includes(userLocation)) {
        const data = await fetchCitiesFromDep(userLocation, query);
        corsicaResults.push(...data);
      }

      return corsicaResults;
    }

    if (!userLocationIsNum) {
      query = {
        nom: userLocation,
        limit: 10,
        ...globalQuery,
      };

      const dataFromCities = await fetchCitiesFromQuery(query);

      const dataFromAssociatedCities = await fetchAssociatedCities(query);

      const associatedCitiesWithCP = await Promise.all(
        dataFromAssociatedCities.map(async (city) => {
          const getDataFromLaPosteAPI = await fetchZipCodeFromLaPosteApi(city.nom);
          return {
            ...city,
            codesPostaux: [getDataFromLaPosteAPI.results[0].code_postal],
          };
        }),
      );

      return [...dataFromCities, ...associatedCitiesWithCP];
    }

    return [];
  } catch (err) {
    console.error(`error fetch: ${err.code} - ${err.message}`);
  }
};

function closeList() {
  const ul = document.getElementsByClassName('search-results')[0];
  if (ul) {
    const blockResult = document.getElementsByClassName('event-filters-results')[0];

    while (ul.firstChild) {
      ul.removeChild(ul.firstChild);
    }

    blockResult.classList.add('hidden');
  }
}

const createResultList = (cities) => {
  const resultList = document.getElementsByClassName('search-results')[0];
  const input = document.getElementById('input-localisation');

  resultList.innerHTML = '';

  if (!cities.length && input.value) {
    const li = document.createElement('li');
    li.classList.add('element-list');
    li.textContent = `${input.value} - pas de résultat.`;

    return resultList.appendChild(li);
  }

  return cities.forEach((res) => {
    res.codesPostaux.forEach((cp) => {
      const li = document.createElement('li');
      let currentIndex = -1;
      const items = Array.from(resultList.querySelectorAll('.element-list'));

      li.classList.add('element-list');
      li.textContent = `${res.nom} - ${cp}`;
      resultList.appendChild(li);

      const updateActiveItem = () => {
        items.forEach((item) => item.classList.remove('active'));
        if (items[currentIndex]) {
          items[currentIndex].classList.add('active');
          items[currentIndex].scrollIntoView({ block: 'nearest' });
        }
      };

      input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          currentIndex = (currentIndex + 1) % items.length;
          updateActiveItem();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          currentIndex = (currentIndex - 1 + items.length) % items.length;
          updateActiveItem();
        } else if (e.key === 'Enter') {
          if (currentIndex >= 0 && currentIndex < items.length) {
            items[currentIndex].click();
          }
        }
      });

      li.addEventListener('click', () => {
        input.value = `${res.nom} - ${res.codesPostaux[0]}`;
        input.dataset.longitude = res.centre.coordinates[0];
        input.dataset.latitude = res.centre.coordinates[1];
        closeList(input);
      });
    });
  });
};

function redirectToCurrentPageWithParams(params) {
  const url = new URL(window.location.href);

  Object.keys(params).forEach((key) => {
    url.searchParams.set(key, params[key]);
  });

  url.searchParams.delete('paged');
  url.searchParams.delete('page');

  window.location.href = url.toString();
}

export const getUserLocationFromButton = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const btnLocalisation = document.getElementById('localisation');

    if (btnLocalisation) {
      // eslint-disable-next-line consistent-return
      btnLocalisation.addEventListener('click', async (event) => {
        event.preventDefault();

        const success = async (position) => {
          try {
            redirectToCurrentPageWithParams({
              lon: position.coords.longitude,
              lat: position.coords.latitude,
            });
          } catch (err) {
            alert(`error get position: ${err.code}: ${err.message}`);
          }
        };

        const error = (err) => alert(`Unable to find position: ${err.message}, ${err.code}`);

        if (!navigator.geolocation) {
          return alert('error geolocation');
        }

        await navigator.geolocation.getCurrentPosition(success, error, {
          enableHighAccuracy: false,
          timeout: 5000,
          maximumAge: 60000,
        });
      });
    }
  });
};

export const getUserLocationFromForm = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementsByClassName('form-location')[0];

    if (form) {
      const input = document.getElementById('input-localisation');
      const blockResult = document.getElementsByClassName('event-filters-results')[0];

      blockResult.style.width = `${input.clientWidth}px`;

      form.elements.location.addEventListener('input', async (e) => {
        const inputValue = e.target.value;

        setTimeout(async () => {
          blockResult.classList.remove('hidden');
          const results = await fetchApiGeoFrance(inputValue);
          createResultList([...results]);
        }, 300);
      });

      const buttonLocationForm = form.lastElementChild;

      if (buttonLocationForm) {
        buttonLocationForm.addEventListener('click', async (e) => {
          e.preventDefault();

          if (input.value && input.dataset.longitude && input.dataset.latitude) {
            redirectToCurrentPageWithParams({
              lon: input.dataset.longitude,
              lat: input.dataset.latitude,
            });
          } else {
            input.focus();
          }
        });
      }
    }
  });
};

document.addEventListener('click', (e) => {
  const searchContainer = document.querySelector('.event-filters-search');

  if (!searchContainer) return;

  if (!searchContainer.contains(e.target)) {
    closeList();
  }
});
