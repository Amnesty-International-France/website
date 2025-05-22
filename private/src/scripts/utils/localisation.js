// eslint-disable-next-line consistent-return
const fetchApiGeoFrance = async (userLocation) => {
  const apiAddress = 'https://geo.api.gouv.fr/communes';
  let queryString = {};
  const userLocationIsCP = /^[0-9]{5}$/.test(userLocation);

  try {
    if (userLocation !== null && typeof userLocation === 'object' && !Array.isArray(userLocation)) {
      queryString = {
        lon: userLocation.longitude,
        lat: userLocation.latitude,
      };
    }

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
    const response = await fetch(`${apiAddress}?${query}`);

    return await response.json();
  } catch (err) {
    console.error(`error fetch: ${err.code} - ${err.message}`);
  }
};

const createResultList = (cities) => {
  const resultList = document.getElementById('search-results');
  const input = document.getElementById('location');
  resultList.innerHTML = '';

  cities.forEach((res) => {
    const li = document.createElement('li');
    li.classList.add('element-list');
    li.textContent = `${res.nom} - ${res.codesPostaux[0]}`;
    resultList.appendChild(li);

    li.addEventListener('click', () => {
      input.value = `${res.nom} - ${res.codesPostaux[0]}`;
      // eslint-disable-next-line no-use-before-define
      closeList(input);
    });
  });
};

function closeList() {
  const ul = document.getElementById('search-results');

  while (ul.firstChild) {
    ul.removeChild(ul.firstChild);
  }
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
            const userPosition = {
              longitude: position.coords.longitude,
              latitude: position.coords.latitude,
            };

            await fetchApiGeoFrance(userPosition);

            const query = new URLSearchParams({
              lon: userPosition.longitude,
              lat: userPosition.latitude,
            });

            window.location.href = `/evenements?${query}`;
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
    const form = document.getElementById('form-location');
    const blockResult = document.getElementById('event-filters-results');

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
        const locationValue = form.elements.location.value;

        await fetchApiGeoFrance(locationValue.trim());
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
