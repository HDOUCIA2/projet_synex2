'use strict';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIconRetina,
  iconUrl: markerIcon,
  shadowUrl: markerShadow
});

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
    const map = L.map(mapElement, { scrollWheelZoom: false }).setView([9.2, 17.4], 6);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
      maxZoom: 19
    }).addTo(map);

    const markers = L.markerClusterGroup({
      chunkedLoading: true,
      maxClusterRadius: 42,
      showCoverageOnHover: false
    });

    mapPoints.forEach(point => {
      const latitude = Number(point.latitude);
      const longitude = Number(point.longitude);

      if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return;
      }

      const marker = L.marker([latitude, longitude]);
      const popup = document.createElement('div');
      const title = document.createElement('strong');
      title.textContent = [point.date, point.eventType].filter(Boolean).join(' — ') || 'Événement';
      popup.appendChild(title);

      const details = [
        ['Lieu', [point.locality, point.province].filter(Boolean).join(', ')],
        ['Sous-événement', point.subEvent],
        ['Acteurs', Array.isArray(point.actors) ? point.actors.join(' / ') : ''],
        ['Impacts renseignés', [
          point.deaths === null ? null : `${point.deaths} décès`,
          point.injuries === null ? null : `${point.injuries} blessés`
        ].filter(Boolean).join(', ')]
      ];

      details.forEach(([label, value]) => {
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

    if (markers.getLayers().length > 0) {
      map.fitBounds(markers.getBounds().pad(0.12), { maxZoom: 9 });
    }
  }
});
