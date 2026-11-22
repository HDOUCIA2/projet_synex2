'use strict';

import ApexCharts from 'apexcharts';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

function renderEmpty(element, message) {
  element.textContent = message;
  element.classList.add('synex-admin-chart-empty');
}

function normalizeProvinceName(name) {
  const normalized = name
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '');
  const aliases = {
    bahrelgazel: 'bahr-el-gazel',
    barhelgazel: 'bahr-el-gazel',
    ndjamenaregion: 'ndjamena',
    ndjamena: 'ndjamena'
  };

  return aliases[normalized] || normalized;
}

function createZoneLabel(name, count) {
  const label = document.createElement('span');
  label.className = 'synex-admin-map-zone-label';
  const province = document.createElement('span');
  province.className = 'synex-admin-map-zone-name';
  province.textContent = name;
  const total = document.createElement('strong');
  total.className = 'synex-admin-map-zone-count';
  total.textContent = new Intl.NumberFormat('fr-FR').format(count);
  label.append(province, total);
  return label;
}

function createMapPopup(title, details) {
  const popup = document.createElement('div');
  const heading = document.createElement('strong');
  heading.textContent = title;
  popup.appendChild(heading);

  details.forEach(([label, value]) => {
    if (value === null || value === undefined || value === '') {
      return;
    }

    const line = document.createElement('p');
    line.className = 'mb-1';
    line.textContent = `${label} : ${value}`;
    popup.appendChild(line);
  });

  return popup;
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

function initializeAdminMap() {
  const mapElement = document.getElementById('synexAdminChadMap');
  if (!mapElement) {
    return;
  }

  const data = window.synexAdminDashboardMap;
  const status = document.getElementById('synexAdminMapStatus');
  if (!data || !Array.isArray(data.points) || !data.provinceCounts || !data.eventTypeColors || !data.geoJsonUrl) {
    throw new Error('Les données cartographiques du tableau de bord administrateur sont absentes.');
  }

  fetch(data.geoJsonUrl)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Le chargement des limites du Tchad a échoué (${response.status}).`);
      }

      return response.json();
    })
    .then(boundaries => {
      if (!Array.isArray(boundaries.features) || boundaries.features.length === 0) {
        throw new Error('Le fichier des limites administratives ne contient aucune province.');
      }

      const provinceCounts = new Map(
        Object.entries(data.provinceCounts).map(([province, count]) => [normalizeProvinceName(province), Number(count)])
      );
      const colorForType = type => (
        Object.prototype.hasOwnProperty.call(data.eventTypeColors, type) ? data.eventTypeColors[type] : '#66798c'
      );
      const countForProvince = feature => (
        provinceCounts.get(normalizeProvinceName(feature.properties?.shapeName || '')) || 0
      );
      const colorForCount = count => {
        if (count >= 50) return '#806300';
        if (count >= 20) return '#AF8807';
        if (count >= 10) return '#D2AD36';
        if (count > 0) return '#E8DCA9';
        return '#F7F5EF';
      };

      const map = L.map(mapElement, {
        maxZoom: 10,
        scrollWheelZoom: false
      });
      const provinces = L.geoJSON(boundaries, {
        attribution: '<a href="https://www.geoboundaries.org/">GeoBoundaries</a> (ODbL 1.0)',
        style: feature => ({
          color: '#ffffff',
          weight: 1.4,
          opacity: 1,
          fillColor: colorForCount(countForProvince(feature)),
          fillOpacity: countForProvince(feature) === 0 ? 0.5 : 0.78
        }),
        onEachFeature: (feature, layer) => {
          const province = feature.properties?.shapeName || 'Province';
          const count = countForProvince(feature);
          layer.bindTooltip(createZoneLabel(province, count), {
            permanent: true,
            direction: 'center',
            className: 'synex-admin-map-zone-tooltip',
            opacity: 1
          });
          layer.bindPopup(createMapPopup(province, [['Observations', new Intl.NumberFormat('fr-FR').format(count)]]));
          layer.on({
            mouseover: () => layer.setStyle({ color: '#806300', weight: 2, fillOpacity: 0.96 }),
            mouseout: () => provinces.resetStyle(layer)
          });
        }
      }).addTo(map);

      const bounds = provinces.getBounds();
      const padding = L.point(26, 26);
      map.setMinZoom(map.getBoundsZoom(bounds, false, padding));
      map.fitBounds(bounds, { padding });

      const provinceLayers = [];
      provinces.eachLayer(layer => provinceLayers.push(layer));
      const locatedPoints = data.points.flatMap(point => {
        const latitude = Number(point.latitude);
        const longitude = Number(point.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
          return [];
        }

        const location = L.latLng(latitude, longitude);
        const province = provinceLayers.find(layer => (
          layer.getBounds().contains(location) &&
          geometryContainsPoint(layer.feature.geometry, latitude, longitude)
        ));

        return province ? [{ point, location }] : [];
      });

      const markers = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 32,
        showCoverageOnHover: false,
        iconCreateFunction: cluster => {
          const typeCounts = cluster.getAllChildMarkers().reduce((counts, marker) => {
            counts[marker.synexEventType] = (counts[marker.synexEventType] || 0) + 1;
            return counts;
          }, {});
          const dominantType = Object.entries(typeCounts).sort((first, second) => second[1] - first[1])[0]?.[0];
          const label = document.createElement('span');
          label.textContent = new Intl.NumberFormat('fr-FR').format(cluster.getChildCount());
          label.style.setProperty('--synex-admin-cluster-color', colorForType(dominantType || ''));

          return L.divIcon({
            html: label,
            className: 'synex-admin-map-cluster',
            iconSize: L.point(36, 36)
          });
        }
      });

      locatedPoints.forEach(({ point, location }) => {
        const type = point.eventType || 'Type non renseigné';
        const marker = L.circleMarker(location, {
          radius: 5,
          color: '#ffffff',
          weight: 1.5,
          fillColor: colorForType(type),
          fillOpacity: 0.96
        });
        marker.synexEventType = type;
        marker.bindPopup(createMapPopup(point.date || 'Observation', [
          ['Type', type],
          ['Province', point.province],
          ['Année', point.year],
          ['Statut', point.status]
        ]));
        markers.addLayer(marker);
      });

      map.addLayer(markers);
      markers.on('clusterclick', event => {
        const clusterMarkers = event.layer.getAllChildMarkers();
        const countsByType = clusterMarkers.reduce((counts, marker) => {
          counts[marker.synexEventType] = (counts[marker.synexEventType] || 0) + 1;
          return counts;
        }, {});
        const popup = document.createElement('div');
        const heading = document.createElement('strong');
        heading.textContent = `${new Intl.NumberFormat('fr-FR').format(clusterMarkers.length)} observations`;
        popup.appendChild(heading);
        Object.entries(countsByType).sort((first, second) => second[1] - first[1]).forEach(([type, count]) => {
          const line = document.createElement('p');
          line.className = 'mb-1';
          line.textContent = `${type} : ${new Intl.NumberFormat('fr-FR').format(count)}`;
          popup.appendChild(line);
        });
        L.popup({ maxWidth: 300 })
          .setLatLng(event.layer.getBounds().getCenter())
          .setContent(popup)
          .openOn(map);
      });

      window.requestAnimationFrame(() => map.invalidateSize());
      if (status) {
        status.textContent = `${boundaries.features.length} provinces affichées · ${new Intl.NumberFormat('fr-FR').format(locatedPoints.length)} points GPS issus de la base de données.`;
      }
    })
    .catch(error => {
      console.error('Impossible d’afficher la carte du tableau de bord administrateur.', error);
      if (status) {
        status.textContent = 'La carte ne peut pas être chargée pour le moment. Les données restent disponibles dans les indicateurs du tableau de bord.';
      }
    });
}

function renderChart(elementId, values, labels, options, emptyMessage) {
  const element = document.getElementById(elementId);
  if (!element) {
    return;
  }

  if (!values.length) {
    renderEmpty(element, emptyMessage);
    return;
  }

  const { chart: chartOptions = {}, xaxis: xaxisOptions = {}, ...chartSpecificOptions } = options;

  new ApexCharts(element, {
    chart: { height: 260, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif', ...chartOptions },
    series: [{ name: 'Observations', data: values }],
    dataLabels: { enabled: false },
    grid: { borderColor: '#eceef2', strokeDashArray: 4 },
    tooltip: { y: { formatter: value => `${value.toLocaleString('fr-FR')} observations` } },
    ...chartSpecificOptions,
    xaxis: { categories: labels, ...xaxisOptions }
  }).render().catch(error => {
    console.error(`Impossible d’afficher le graphique ${elementId}.`, error);
    renderEmpty(element, 'Le graphique n’a pas pu être affiché.');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const data = window.synexAdminDashboardCharts;
  if (!data) {
    throw new Error('Les données du tableau de bord administrateur sont absentes.');
  }

  renderChart(
    'synexAdminAnnualChart',
    data.annual.values,
    data.annual.labels,
    {
      chart: { type: 'bar' },
      colors: ['#c99335'],
      plotOptions: { bar: { columnWidth: '48%', borderRadius: 4 } },
      xaxis: { labels: { style: { colors: '#697386' } } },
      yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: '#697386' } } }
    },
    'Aucune donnée annuelle disponible.'
  );

  renderChart(
    'synexAdminProvinceChart',
    data.provinces.values,
    data.provinces.labels,
    {
      chart: { type: 'bar' },
      colors: ['#4d657b'],
      plotOptions: { bar: { horizontal: true, barHeight: '54%', borderRadius: 3 } },
      xaxis: { labels: { style: { colors: '#697386' } } },
      yaxis: { labels: { style: { colors: '#697386' } } }
    },
    'Aucune province renseignée.'
  );

  initializeAdminMap();

  const themeElement = document.getElementById('synexAdminThemeChart');
  if (!themeElement) {
    return;
  }
  if (!data.themes.values.length) {
    renderEmpty(themeElement, 'Aucune thématique renseignée.');
    return;
  }

  new ApexCharts(themeElement, {
    chart: { type: 'donut', height: 260, fontFamily: 'Public Sans, sans-serif' },
    series: data.themes.values,
    labels: data.themes.labels,
    colors: ['#c99335', '#526d82', '#9fa9b4', '#d8b56b', '#48545f', '#82a4a4', '#ba8b73'],
    dataLabels: { enabled: false },
    legend: { position: 'bottom', fontSize: '11px', markers: { offsetX: -3 } },
    stroke: { width: 3, colors: ['#fff'] },
    plotOptions: { pie: { donut: { size: '62%' } } },
    tooltip: { y: { formatter: value => `${value.toLocaleString('fr-FR')} observations` } }
  }).render().catch(error => {
    console.error('Impossible d’afficher le graphique des thématiques.', error);
    renderEmpty(themeElement, 'Le graphique n’a pas pu être affiché.');
  });
});
