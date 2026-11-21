import ApexCharts from 'apexcharts';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

function normalizeProvinceName(name) {
  return String(name || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '');
}

function pointInRing(longitude, latitude, ring) {
  let inside = false;

  for (let i = 0, j = ring.length - 1; i < ring.length; j = i, i += 1) {
    const [currentLongitude, currentLatitude] = ring[i];
    const [previousLongitude, previousLatitude] = ring[j];
    const crossesLatitude = (currentLatitude > latitude) !== (previousLatitude > latitude);

    if (
      crossesLatitude &&
      longitude < ((previousLongitude - currentLongitude) * (latitude - currentLatitude)) / (previousLatitude - currentLatitude) + currentLongitude
    ) {
      inside = !inside;
    }
  }

  return inside;
}

function geometryContainsPoint(geometry, latitude, longitude) {
  const polygons = geometry.type === 'Polygon' ? [geometry.coordinates] : geometry.coordinates;

  return polygons.some(polygon => (
    pointInRing(longitude, latitude, polygon[0]) &&
    !polygon.slice(1).some(hole => pointInRing(longitude, latitude, hole))
  ));
}

function createProvincePopup(name, count) {
  const popup = document.createElement('div');
  popup.className = 'veille-map-popup';

  const title = document.createElement('strong');
  title.textContent = name;
  popup.appendChild(title);

  const total = document.createElement('p');
  total.textContent = `${new Intl.NumberFormat('fr-FR').format(count)} événement${count > 1 ? 's' : ''} approuvé${count > 1 ? 's' : ''}`;
  popup.appendChild(total);

  return popup;
}

function createEventPopup(point) {
  const popup = document.createElement('div');
  popup.className = 'veille-map-popup';

  const title = document.createElement('strong');
  title.textContent = point.eventType || 'Type non renseigné';
  popup.appendChild(title);

  const location = document.createElement('p');
  location.textContent = [point.locality, point.province].filter(Boolean).join(', ') || 'Lieu non renseigné';
  popup.appendChild(location);

  if (point.date) {
    const date = document.createElement('p');
    date.textContent = point.date;
    popup.appendChild(date);
  }

  return popup;
}

function initializeVeilleMap(data) {
  const mapElement = document.getElementById('synexVeilleMap');

  if (!mapElement) {
    return;
  }

  const status = document.getElementById('synexVeilleMapStatus');
  const fail = error => {
    status.textContent = 'La carte du Tchad n’a pas pu être chargée.';
    console.error('Unable to initialize the Veille map.', error);
  };

  if (!data || !Array.isArray(data.points) || !data.provinceEventCounts || !data.geoJsonUrl) {
    fail(new Error('Veille map data is missing or invalid.'));
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

      const eventCounts = new Map(
        Object.entries(data.provinceEventCounts).map(([province, count]) => [normalizeProvinceName(province), Number(count)])
      );
      const countForFeature = feature => eventCounts.get(normalizeProvinceName(feature.properties.shapeName)) || 0;
      const colorForCount = count => {
        if (count >= 50) return '#806300';
        if (count >= 20) return '#AF8807';
        if (count >= 10) return '#D2AD36';
        if (count > 0) return '#E8DCA9';
        return '#F7F5EF';
      };
      const map = L.map(mapElement, { maxZoom: 11, scrollWheelZoom: false });
      const provinces = L.geoJSON(boundaries, {
        attribution:
          '<a href="https://www.geoboundaries.org/">GeoBoundaries</a> (OpenStreetMap/Wambacher, ODbL 1.0)',
        style: feature => ({
          color: '#FFFFFF',
          weight: 1.2,
          opacity: 1,
          fillColor: colorForCount(countForFeature(feature)),
          fillOpacity: countForFeature(feature) === 0 ? 0.5 : 0.78
        }),
        onEachFeature: (feature, layer) => {
          const provinceName = feature.properties.shapeName || 'Province';
          const count = countForFeature(feature);
          layer.bindTooltip(`${provinceName} : ${new Intl.NumberFormat('fr-FR').format(count)} événement${count > 1 ? 's' : ''}`);
          layer.bindPopup(createProvincePopup(provinceName, count));
          layer.on({
            mouseover: () => layer.setStyle({ weight: 2, color: '#806300', fillOpacity: 0.9 }),
            mouseout: () => provinces.resetStyle(layer)
          });
        }
      }).addTo(map);

      const bounds = provinces.getBounds();
      map.fitBounds(bounds, { padding: [18, 18] });
      map.setMaxBounds(bounds.pad(0.05));

      const provinceLayers = [];
      provinces.eachLayer(layer => provinceLayers.push(layer));
      const locatedPoints = data.points.flatMap(point => {
        const latitude = Number(point.latitude);
        const longitude = Number(point.longitude);

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
          return [];
        }

        const location = L.latLng(latitude, longitude);
        const isInsideChad = provinceLayers.some(layer => (
          layer.getBounds().contains(location) &&
          geometryContainsPoint(layer.feature.geometry, latitude, longitude)
        ));

        return isInsideChad ? [{ point, location }] : [];
      });

      const typeColors = ['#D64545', '#3985B9', '#198754', '#E49A20', '#806300', '#AF8807'];
      const eventTypes = [...new Set(locatedPoints.map(({ point }) => point.eventType || 'Type non renseigné'))];
      const colorByType = new Map(eventTypes.map((type, index) => [type, typeColors[index % typeColors.length]]));

      locatedPoints.forEach(({ point, location }) => {
        L.circleMarker(location, {
          radius: 5,
          color: '#FFFFFF',
          weight: 1.2,
          fillColor: colorByType.get(point.eventType || 'Type non renseigné'),
          fillOpacity: 0.95
        })
          .bindPopup(createEventPopup(point))
          .addTo(map);
      });

      status.textContent = `${new Intl.NumberFormat('fr-FR').format(locatedPoints.length)} repère(s) affiché(s) sur la carte du Tchad.`;
      window.setTimeout(() => map.invalidateSize(), 0);
    })
    .catch(fail);
}

function initializeVeilleCharts(data) {
  if (!data || !data.trend || !Array.isArray(data.trend.labels) || !Array.isArray(data.trend.values)) {
    throw new Error('Veille chart data is missing or invalid.');
  }

  const primary = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim() || '#AF8807';
  const muted = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary-color').trim() || '#77736A';
  const grid = getComputedStyle(document.documentElement).getPropertyValue('--bs-border-color').trim() || '#E8E1CF';
  const render = (id, options) => {
    const element = document.getElementById(id);

    if (!element) {
      return;
    }

    new ApexCharts(element, options).render().catch(error => {
      element.textContent = 'Les données graphiques ne peuvent pas être affichées.';
      console.error(`Unable to render ${id}.`, error);
    });
  };

  render('synexVeilleTrendChart', {
    chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Événements suivis', data: data.trend.values }],
    colors: [primary],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.03 } },
    grid: { borderColor: grid, strokeDashArray: 4 },
    xaxis: { categories: data.trend.labels, labels: { style: { colors: muted } } },
    yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: muted } } },
    tooltip: { y: { formatter: value => `${value} événement(s)` } }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const veilleData = window.synexVeilleData;
  initializeVeilleMap(veilleData);
  initializeVeilleCharts(veilleData);

  const deathsInput = document.getElementById('recorded-deaths');
  const populationInput = document.getElementById('territory-population');
  const rateResult = document.getElementById('death-rate-result');
  const rateValue = document.getElementById('death-rate-value');
  const rateDescription = document.getElementById('death-rate-description');
  const rateSeverity = document.getElementById('death-rate-severity');
  const thresholdCards = document.querySelectorAll('[data-death-level]');

  if (deathsInput && populationInput && rateResult && rateValue && rateDescription && rateSeverity) {
    const resetLevel = () => {
      thresholdCards.forEach(card => {
        card.classList.remove('border-primary', 'border-3', 'shadow-sm');
        card.removeAttribute('aria-current');
      });
      rateResult.classList.remove('bg-label-success', 'bg-label-warning', 'bg-label-danger');
      rateResult.classList.add('bg-label-primary');
      rateSeverity.classList.remove('bg-label-success', 'bg-label-warning', 'bg-label-danger');
      rateSeverity.classList.add('bg-label-secondary');
    };

    const calculateRate = () => {
      const deathsRaw = deathsInput.value.trim();
      const populationRaw = populationInput.value.trim();

      rateResult.dataset.level = '';
      resetLevel();

      if (deathsRaw === '' || populationRaw === '') {
        rateValue.textContent = '—';
        rateDescription.textContent = 'décès / 100 000 habitants';
        rateSeverity.textContent = 'Saisissez les deux valeurs';
        return;
      }

      const deaths = Number(deathsRaw);
      const population = Number(populationRaw);

      if (!Number.isSafeInteger(deaths) || deaths < 0 || !Number.isSafeInteger(population) || population <= 0) {
        rateValue.textContent = '!';
        rateDescription.textContent = 'Vérifiez les valeurs saisies.';
        rateSeverity.textContent = 'Valeurs invalides';
        rateSeverity.classList.replace('bg-label-secondary', 'bg-label-danger');
        rateResult.dataset.level = 'invalid';
        rateResult.classList.replace('bg-label-primary', 'bg-label-danger');
        return;
      }

      const rate = (deaths / population) * 100000;

      if (!Number.isFinite(rate)) {
        rateValue.textContent = '!';
        rateDescription.textContent = 'Vérifiez les valeurs saisies.';
        rateSeverity.textContent = 'Calcul impossible';
        rateSeverity.classList.replace('bg-label-secondary', 'bg-label-danger');
        rateResult.dataset.level = 'invalid';
        rateResult.classList.replace('bg-label-primary', 'bg-label-danger');
        return;
      }

      const level =
        rate < 3
          ? { key: 'low', label: 'Faible' }
          : rate <= 8
            ? { key: 'moderate', label: 'Modérée' }
            : { key: 'high', label: 'Élevée' };

      rateValue.textContent = rate.toFixed(1);
      rateDescription.textContent = 'décès / 100 000 habitants';
      rateSeverity.textContent = level.label;
      rateSeverity.classList.replace(
        'bg-label-secondary',
        level.key === 'low' ? 'bg-label-success' : level.key === 'moderate' ? 'bg-label-warning' : 'bg-label-danger'
      );
      rateResult.dataset.level = level.key;
      rateResult.classList.replace(
        'bg-label-primary',
        level.key === 'low' ? 'bg-label-success' : level.key === 'moderate' ? 'bg-label-warning' : 'bg-label-danger'
      );
      thresholdCards.forEach(card => {
        if (card.dataset.deathLevel === level.key) {
          card.classList.add('border-primary', 'border-3', 'shadow-sm');
          card.setAttribute('aria-current', 'true');
        }
      });
    };

    deathsInput.addEventListener('input', calculateRate);
    populationInput.addEventListener('input', calculateRate);
  }

  const elements = document.querySelectorAll('[data-veille-animation]');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (reducedMotion || !('IntersectionObserver' in window)) {
    elements.forEach(element => element.classList.add('is-visible'));
    return;
  }

  document.documentElement.classList.add('veille-motion-ready');
  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );

  elements.forEach(element => {
    if (element.dataset.veilleAnimation === 'none') {
      element.classList.add('is-visible');
    } else {
      observer.observe(element);
    }
  });
});
