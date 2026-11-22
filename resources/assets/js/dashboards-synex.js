'use strict';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

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

document.addEventListener('DOMContentLoaded', function () {
  const data = window.synexDashboardCharts;
  const mapPoints = window.synexMapPoints;

  if (!data) {
    throw new Error('Synex dashboard chart data is missing.');
  }

  if (!Array.isArray(mapPoints)) {
    throw new Error('Synex dashboard map points are missing.');
  }

  const theme = getComputedStyle(document.documentElement);
  const colors = {
    primary: theme.getPropertyValue('--bs-primary').trim(),
    warning: theme.getPropertyValue('--bs-warning').trim(),
    success: theme.getPropertyValue('--bs-success').trim(),
    danger: theme.getPropertyValue('--bs-danger').trim(),
    info: theme.getPropertyValue('--bs-info').trim(),
    muted: theme.getPropertyValue('--bs-secondary-color').trim(),
    grid: theme.getPropertyValue('--bs-border-color').trim()
  };

  const render = (id, options) => {
    const element = document.getElementById(id);

    if (!element) {
      return;
    }

    new ApexCharts(element, options).render();
  };

  render('eventsByYearChart', {
    chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Événements', data: data.eventsByYear.values }],
    colors: [colors.primary],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.04 } },
    grid: { borderColor: colors.grid, strokeDashArray: 4 },
    xaxis: { categories: data.eventsByYear.labels, labels: { style: { colors: colors.muted } } },
    yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: colors.muted } } },
    tooltip: { y: { formatter: value => `${value} événements` } }
  });

  render('eventsByProvinceChart', {
    chart: { type: 'donut', height: 320, fontFamily: 'Public Sans, sans-serif' },
    series: data.eventsByProvince.values,
    labels: data.eventsByProvince.labels,
    colors: [colors.primary, colors.info, colors.warning, colors.success],
    legend: { position: 'bottom', labels: { colors: colors.muted } },
    dataLabels: { enabled: true },
    stroke: { colors: ['#fff'] },
    tooltip: { y: { formatter: value => `${value} événements` } }
  });

  render('eventsByTypeChart', {
    chart: { type: 'bar', height: 360, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Événements', data: data.eventsByType.values }],
    colors: [colors.primary],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '62%', distributed: true } },
    dataLabels: { enabled: false },
    xaxis: { categories: data.eventsByType.labels, labels: { style: { colors: colors.muted } } },
    yaxis: { labels: { style: { colors: colors.muted } } },
    grid: { borderColor: colors.grid, strokeDashArray: 4 },
    legend: { show: false },
    tooltip: { y: { formatter: value => `${value} événements` } }
  });

  render('eventsByThemeChart', {
    chart: { type: 'bar', height: 360, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Événements', data: data.eventsByTheme.values }],
    colors: [colors.info],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '62%' } },
    dataLabels: { enabled: false },
    xaxis: { categories: data.eventsByTheme.labels, labels: { style: { colors: colors.muted } } },
    yaxis: { labels: { style: { colors: colors.muted } } },
    grid: { borderColor: colors.grid, strokeDashArray: 4 },
    legend: { show: false },
    tooltip: { y: { formatter: value => `${value} événements` } }
  });

  render('recordedImpactsChart', {
    chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Valeurs renseignées', data: data.impacts.values }],
    colors: [colors.danger, colors.warning, colors.success],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, barHeight: '48%' } },
    dataLabels: { enabled: true },
    xaxis: { categories: data.impacts.labels, min: 0, forceNiceScale: true, labels: { style: { colors: colors.muted } } },
    yaxis: { labels: { style: { colors: colors.muted } } },
    grid: { borderColor: colors.grid, strokeDashArray: 4 },
    legend: { show: false },
    tooltip: { y: { formatter: value => value.toLocaleString('fr-FR') } }
  });

  render('deathsPer100kChart', {
    chart: { type: 'bar', height: 360, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
    series: [{ name: 'Décès pour 100 000 habitants', data: data.deathRate.values }],
    colors: [colors.danger],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '58%' } },
    dataLabels: { enabled: true, formatter: value => Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 2 }) },
    xaxis: { categories: data.deathRate.labels, min: 0, forceNiceScale: true, labels: { style: { colors: colors.muted } } },
    yaxis: { labels: { style: { colors: colors.muted } } },
    grid: { borderColor: colors.grid, strokeDashArray: 4 },
    legend: { show: false },
    tooltip: { y: { formatter: value => `${Number(value).toLocaleString('fr-FR', { maximumFractionDigits: 2 })} décès pour 100 000 habitants` } }
  });

  const mapElement = document.getElementById('synexEventsMap');

  if (mapElement) {
    const status = document.getElementById('synexEventsMapStatus');
    const provinceCounts = window.synexMapProvinceCounts;

    if (!provinceCounts || !window.synexMapGeoJsonUrl) {
      throw new Error('Synex dashboard province map data is missing.');
    }

    fetch(window.synexMapGeoJsonUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Le chargement des provinces du Tchad a échoué (${response.status}).`);
        }

        return response.json();
      })
      .then(boundaries => {
        if (!Array.isArray(boundaries.features) || boundaries.features.length === 0) {
          throw new Error('Le fichier des limites administratives ne contient aucune province.');
        }

        const counts = new Map(
          Object.entries(provinceCounts).map(([province, count]) => [normalizeProvinceName(province), Number(count)])
        );
        const countForProvince = feature => counts.get(normalizeProvinceName(feature.properties?.shapeName || '')) || 0;
        const colorForCount = count => {
          if (count >= 50) return '#806300';
          if (count >= 20) return '#AF8807';
          if (count >= 10) return '#D2AD36';
          if (count > 0) return '#E8DCA9';
          return '#F7F5EF';
        };
        const map = L.map(mapElement, { maxZoom: 10, scrollWheelZoom: false });
        const provinces = L.geoJSON(boundaries, {
          attribution: '<a href="https://www.geoboundaries.org/">GeoBoundaries</a> (ODbL 1.0)',
          style: feature => ({
            color: '#FFFFFF',
            weight: 1.2,
            fillColor: colorForCount(countForProvince(feature)),
            fillOpacity: countForProvince(feature) > 0 ? .78 : .5
          }),
          onEachFeature: (feature, layer) => {
            const name = feature.properties?.shapeName || 'Province';
            const count = countForProvince(feature);
            const tooltip = document.createElement('span');
            tooltip.textContent = `${name} : ${new Intl.NumberFormat('fr-FR').format(count)} observation${count > 1 ? 's' : ''}`;
            layer.bindTooltip(tooltip);
            layer.on({
              mouseover: () => layer.setStyle({ color: '#806300', weight: 2, fillOpacity: .92 }),
              mouseout: () => provinces.resetStyle(layer)
            });
          }
        }).addTo(map);

        const bounds = provinces.getBounds();
        const padding = L.point(18, 18);
        map.setMinZoom(map.getBoundsZoom(bounds, false, padding));
        map.fitBounds(bounds, { padding });

        const provinceLayers = [];
        provinces.eachLayer(layer => provinceLayers.push(layer));
        const locatedPoints = mapPoints.flatMap(point => {
          const latitude = Number(point.latitude);
          const longitude = Number(point.longitude);
          if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
            return [];
          }

          const location = L.latLng(latitude, longitude);
          const insideChad = provinceLayers.some(layer => (
            layer.getBounds().contains(location) &&
            geometryContainsPoint(layer.feature.geometry, latitude, longitude)
          ));

          return insideChad ? [{ point, location }] : [];
        });
        const typeCounts = locatedPoints.reduce((countsByType, { point }) => {
          const type = point.eventType || 'Type non renseigné';
          countsByType[type] = (countsByType[type] || 0) + 1;
          return countsByType;
        }, {});
        const typePalette = ['#D64545', '#E49A20', '#3985B9', '#7460A8', '#31836F', '#CB5D88', '#66798C', '#AA7A36'];
        const typeColors = new Map(
          Object.entries(typeCounts)
            .sort((first, second) => second[1] - first[1])
            .map(([type], index) => [type, typePalette[index % typePalette.length]])
        );
        const typeLegend = document.getElementById('synexDashboardMapTypeLegend');
        if (typeLegend) {
          const typeEntries = Object.entries(typeCounts).sort((first, second) => second[1] - first[1]);
          if (typeEntries.length === 0) {
            const empty = document.createElement('span');
            empty.textContent = 'Aucun point GPS disponible';
            typeLegend.appendChild(empty);
          }
          typeEntries.forEach(([type]) => {
            const entry = document.createElement('span');
            const swatch = document.createElement('i');
            swatch.className = 'synex-dashboard-map-type-swatch';
            swatch.style.setProperty('--synex-dashboard-type-color', typeColors.get(type));
            const label = document.createElement('span');
            label.textContent = type;
            entry.append(swatch, label);
            typeLegend.appendChild(entry);
          });
        }

        const markers = L.markerClusterGroup({
          chunkedLoading: true,
          maxClusterRadius: 32,
          showCoverageOnHover: false,
          iconCreateFunction: cluster => {
            const clusterTypes = cluster.getAllChildMarkers().reduce((types, marker) => {
              types[marker.synexEventType] = (types[marker.synexEventType] || 0) + 1;
              return types;
            }, {});
            const dominantType = Object.entries(clusterTypes).sort((first, second) => second[1] - first[1])[0]?.[0];
            const label = document.createElement('span');
            label.textContent = new Intl.NumberFormat('fr-FR').format(cluster.getChildCount());
            label.style.setProperty('--synex-dashboard-cluster-color', typeColors.get(dominantType) || '#66798C');

            return L.divIcon({
              html: label,
              className: 'synex-dashboard-map-cluster',
              iconSize: L.point(34, 34)
            });
          }
        });

        locatedPoints.forEach(({ point, location }) => {
          const type = point.eventType || 'Type non renseigné';
          const marker = L.circleMarker(location, {
            radius: 5,
            color: '#FFFFFF',
            weight: 1.4,
            fillColor: typeColors.get(type),
            fillOpacity: .95
          });
          marker.synexEventType = type;

          const popup = document.createElement('div');
          const title = document.createElement('strong');
          title.textContent = [point.date, type].filter(Boolean).join(' — ') || 'Événement';
          popup.appendChild(title);
          [
            ['Lieu', [point.locality, point.province].filter(Boolean).join(', ')],
            ['Sous-événement', point.subEvent],
            ['Acteurs', Array.isArray(point.actors) ? point.actors.join(' / ') : ''],
            ['Impacts renseignés', [
              point.deaths === null ? null : `${point.deaths} décès`,
              point.injuries === null ? null : `${point.injuries} blessés`
            ].filter(Boolean).join(', ')]
          ].forEach(([label, value]) => {
            if (!value) {
              return;
            }

            const line = document.createElement('p');
            line.className = 'mb-1';
            line.textContent = `${label} : ${value}`;
            popup.appendChild(line);
          });
          marker.bindPopup(popup);
          markers.addLayer(marker);
        });

        map.addLayer(markers);
        window.requestAnimationFrame(() => map.invalidateSize());
        if (status) {
          status.textContent = `${boundaries.features.length} provinces affichées · ${new Intl.NumberFormat('fr-FR').format(locatedPoints.length)} points GPS issus des observations filtrées.`;
        }
      })
      .catch(error => {
        console.error('Unable to render the SYNEX dashboard map.', error);
        if (status) {
          status.textContent = 'La carte du Tchad ne peut pas être chargée pour le moment.';
        }
      });
  }
});
