import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

function initializeCareMap() {
  const element = document.getElementById('care-map');
  if (!element) return;

  const status = document.getElementById('care-map-status');
  const data = window.synexCareMapData;
  const fail = error => {
    status.textContent = 'La carte des structures ne peut pas être chargée pour le moment.';
    console.error('Unable to initialize the care services map.', error);
  };

  if (!data || !Array.isArray(data.centers) || !data.geoJsonUrl) {
    fail(new Error('Care map data is missing or invalid.'));
    return;
  }

  fetch(data.geoJsonUrl)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Chad boundary request failed with status ${response.status}.`);
      }

      return response.json();
    })
    .then(boundaries => {
      if (!Array.isArray(boundaries.features) || boundaries.features.length === 0) {
        throw new Error('Chad boundary data contains no administrative zones.');
      }

      const map = L.map(element, {
        maxZoom: 10,
        scrollWheelZoom: false
      });
      const provinces = L.geoJSON(boundaries, {
        attribution:
          '<a href="https://www.geoboundaries.org/">GeoBoundaries</a> (OpenStreetMap/Wambacher, ODbL 1.0)',
        style: {
          color: '#ffffff',
          weight: 1,
          fillColor: '#e8d9b3',
          fillOpacity: 0.8
        },
        onEachFeature: (feature, layer) => {
          if (feature.properties?.shapeName) {
            layer.bindTooltip(feature.properties.shapeName);
          }
        }
      }).addTo(map);

      const bounds = provinces.getBounds();
      map.fitBounds(bounds, { padding: [12, 12] });
      map.setMaxBounds(bounds.pad(0.08));

      data.centers.forEach(center => {
        const latitude = Number(center.latitude);
        const longitude = Number(center.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;

        const marker = L.circleMarker([latitude, longitude], {
          radius: 6,
          color: '#ffffff',
          weight: 2,
          fillColor: '#b47709',
          fillOpacity: 1
        });
        const popup = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = center.name;
        popup.appendChild(name);
        const locality = [center.locality, center.department, center.province].filter(Boolean).join(', ');
        if (locality) {
          const location = document.createElement('p');
          location.className = 'mb-0';
          location.textContent = locality;
          popup.appendChild(location);
        }
        marker.bindPopup(popup).addTo(map);
      });

      status.textContent = data.centers.length
        ? `${data.centers.length} structure(s) autorisée(s) affichée(s).`
        : 'Aucune structure localisée et autorisée à être affichée pour le moment.';
    })
    .catch(fail);
}

initializeCareMap();
