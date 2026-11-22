'use strict';

import ApexCharts from 'apexcharts';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

function normalizeProvince(name) {
  return String(name || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '');
}

function showEmptyMessage(element, message) {
  element.textContent = message;
  element.classList.add('synex-dashboard-two-empty');
}

document.addEventListener('DOMContentLoaded', () => {
  const data = window.synexDashboardTwoData;

  if (!data) {
    throw new Error('Les données du tableau de bord SYNEX 2 sont absentes.');
  }

  const annualElement = document.getElementById('synexDashboardTwoAnnualChart');
  if (data.annualTrend.values.length) {
    const annualMaximum = Math.max(...data.annualTrend.values);

    new ApexCharts(annualElement, {
      chart: { type: 'bar', height: 230, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
      series: [{ name: 'Observations', data: data.annualTrend.values }],
      colors: ['#d58a08'],
      plotOptions: {
        bar: {
          columnWidth: '58%',
          borderRadius: 2,
          distributed: false,
          dataLabels: { position: 'top' }
        }
      },
      dataLabels: { enabled: true, offsetY: -6, style: { colors: ['#202536'], fontSize: '11px' } },
      xaxis: { categories: data.annualTrend.labels, labels: { style: { colors: '#606879', fontSize: '10px' } } },
      yaxis: {
        min: 0,
        max: Math.ceil(annualMaximum * 1.2),
        forceNiceScale: true,
        labels: { style: { colors: '#606879', fontSize: '10px' } }
      },
      grid: { borderColor: '#e8eaf0', strokeDashArray: 3, padding: { top: 14 } },
      tooltip: { y: { formatter: value => `${value.toLocaleString('fr-FR')} observations` } }
    }).render();
  } else {
    showEmptyMessage(annualElement, 'Aucune donnée annuelle disponible.');
  }

  const themeElement = document.getElementById('synexDashboardTwoThemeChart');
  if (data.themeDistribution.length) {
    new ApexCharts(themeElement, {
      chart: { type: 'donut', height: 218, fontFamily: 'Public Sans, sans-serif' },
      series: data.themeDistribution.map(theme => theme.count),
      labels: data.themeDistribution.map(theme => theme.label),
      colors: data.themeDistribution.map(theme => theme.color),
      dataLabels: { enabled: false },
      legend: { show: false },
      stroke: { width: 2, colors: ['#fff'] },
      plotOptions: {
        pie: {
          donut: {
            size: '62%',
            labels: {
              show: true,
              total: { show: true, label: 'observations', color: '#202536', fontSize: '11px' }
            }
          }
        }
      },
      tooltip: { y: { formatter: value => `${value.toLocaleString('fr-FR')} observations` } }
    }).render();
  } else {
    showEmptyMessage(themeElement, 'Aucune thématique disponible.');
  }

  const mapElement = document.getElementById('synexDashboardTwoMap');
  const provinceCounts = new Map(
    data.provinceDistribution.map(item => [normalizeProvince(item.province), Number(item.count)])
  );
  const map = L.map(mapElement, { zoomControl: false, scrollWheelZoom: false, attributionControl: false });
  const colorForCount = count => {
    if (count >= 50) return '#806300';
    if (count >= 20) return '#AF8807';
    if (count >= 10) return '#D2AD36';
    if (count > 0) return '#E8DCA9';
    return '#F7F5EF';
  };

  fetch(mapElement.dataset.geojsonUrl)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Impossible de charger les limites des provinces (${response.status}).`);
      }

      return response.json();
    })
    .then(geoJson => {
      const provinces = L.geoJSON(geoJson, {
        style: feature => {
          const count = provinceCounts.get(normalizeProvince(feature.properties?.shapeName)) || 0;

          return {
            color: '#FFFFFF',
            weight: 1.2,
            fillColor: colorForCount(count),
            fillOpacity: count ? .78 : .5
          };
        },
        onEachFeature: (feature, layer) => {
          const name = feature.properties?.shapeName || 'Province';
          const count = provinceCounts.get(normalizeProvince(name)) || 0;
          layer.bindTooltip(`${name} : ${count.toLocaleString('fr-FR')} observation${count === 1 ? '' : 's'}`);
        }
      }).addTo(map);

      map.fitBounds(provinces.getBounds(), { padding: [4, 4] });
    })
    .catch(error => {
      showEmptyMessage(mapElement, 'La carte des provinces est momentanément indisponible.');
      console.error(error);
    });
});
