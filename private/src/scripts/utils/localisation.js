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
  } catch (e) {
    return null;
  }
};

// eslint-disable-next-line consistent-return
const fetchApiGeoFrance = async (userLocation) => {
  const defaultApiGouvUrl = 'https://geo.api.gouv.fr';
  const apiGeoAddress = `${defaultApiGouvUrl}/communes`;

  let queryString = {};
  const userLocationIsCP = /^[0-9]{5}$/.test(userLocation);

  try {
    if (userLocation !== null && userLocationIsCP) {
      queryString = {
        codePostal: userLocation,
        fields: 'codesPostaux,nom,centre',
        limit: 10,
      };
    }

    if (userLocation !== null && !userLocationIsCP) {
      queryString = {
        nom: userLocation,
        fields: 'codesPostaux,nom,centre',
        limit: 10,
      };
    }

    const query = new URLSearchParams(queryString).toString();
    const responseFromCities = await fetch(`${apiGeoAddress}?${query}`);
    const dataFromCities = await responseFromCities.json();

    if (dataFromCities.length === 0) {
      const apiAssociatedCities = `${defaultApiGouvUrl}/communes_associees_deleguees`;

      const responseFromAssociatedCities = await fetch(`${apiAssociatedCities}?${query}`);
      const dataFromAssociatedCities = await responseFromAssociatedCities.json();

      return await Promise.all(
        dataFromAssociatedCities.map(async (city) => {
          const getDataFromLaPosteAPI = await fetchZipCodeFromLaPosteApi(city.nom);
          return {
            ...city,
            codesPostaux: [getDataFromLaPosteAPI.results[0].code_postal],
          };
        }),
      );
    }

    return dataFromCities;
  } catch (err) {
    console.error(`error fetch: ${err.code} - ${err.message}`);
  }
};

function closeList() {
  const ul = document.getElementsByClassName('search-results')[0];
  const blockResult = document.getElementsByClassName('event-filters-results')[0];

  while (ul.firstChild) {
    ul.removeChild(ul.firstChild);
  }

  blockResult.classList.add('hidden');
}

const createResultList = (cities) => {
  const resultList = document.getElementsByClassName('search-results')[0];
  const input = document.getElementById('input-localisation');

  resultList.innerHTML = '';

  cities.forEach((res) => {
    const li = document.createElement('li');
    li.classList.add('element-list');
    li.textContent = `${res.nom} - ${res.codesPostaux[0]}`;
    resultList.appendChild(li);

    li.addEventListener('click', () => {
      input.value = `${res.nom} - ${res.codesPostaux[0]}`;
      input.dataset.longitude = res.centre.coordinates[0];
      input.dataset.latitude = res.centre.coordinates[1];
      closeList(input);
    });
  });
};

function redirectToEventsListWithParams(lon, lat) {
  const query = new URLSearchParams({
    lon,
    lat,
  });

  window.location.href = `/evenements?${query}`;
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
            redirectToEventsListWithParams(position.coords.longitude, position.coords.latitude);
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

        redirectToEventsListWithParams(
          form.elements.location.attributes['data-longitude'].value,
          form.elements.location.attributes['data-latitude'].value,
        );
      });
    }
  });
};
document.addEventListener('click', (e) => {
  closeList(e.target);
});

export default {
  getUserLocationFromButton,
  getUserLocationFromForm,
};
