'use strict';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

function normalizeProvinceName(name) {
  return name
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '');
}

function normalizeEventType(type) {
  return typeof type === 'string' && type.trim() ? type.trim() : 'Type non renseigné';
}

function createMapPopup(title, count, typeCounts, typeColors) {
  const popup = document.createElement('div');
  popup.className = 'synex-home2-map-popup';

  const heading = document.createElement('strong');
  heading.className = 'synex-home2-map-popup-title';
  heading.textContent = title;
  popup.appendChild(heading);

  const total = document.createElement('p');
  total.className = 'synex-home2-map-popup-total';
  total.textContent = `Nombre d’événements : ${new Intl.NumberFormat('fr-FR').format(count)}`;
  popup.appendChild(total);

  const entries = Object.entries(typeCounts || {})
    .map(([type, total]) => [type, Number(total)])
    .filter(([, total]) => Number.isFinite(total) && total > 0)
    .sort((first, second) => second[1] - first[1]);

  if (entries.length === 0) {
    const empty = document.createElement('p');
    empty.className = 'mb-0 text-muted';
    empty.textContent = 'Aucun type d’événement renseigné.';
    popup.appendChild(empty);
    return popup;
  }

  const typeHeading = document.createElement('p');
  typeHeading.className = 'synex-home2-map-popup-types-title';
  typeHeading.textContent = 'Événements par type';
  popup.appendChild(typeHeading);

  entries.forEach(([type, total]) => {
    const row = document.createElement('div');
    row.className = 'synex-home2-map-popup-type';

    const label = document.createElement('span');
    label.className = 'synex-home2-map-popup-type-label';

    const dot = document.createElement('span');
    dot.className = 'synex-home2-dot';
    const color = Object.prototype.hasOwnProperty.call(typeColors, type) ? typeColors[type] : '#AF8807';
    dot.style.setProperty('--synex-home2-type-color', color);
    label.appendChild(dot);

    const text = document.createElement('span');
    text.textContent = type;
    label.appendChild(text);
    row.appendChild(label);

    const value = document.createElement('strong');
    value.textContent = new Intl.NumberFormat('fr-FR').format(total);
    row.appendChild(value);
    popup.appendChild(row);
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

  return polygons.some(polygon => {
    if (!pointInRing(longitude, latitude, polygon[0])) {
      return false;
    }

    return !polygon.slice(1).some(hole => pointInRing(longitude, latitude, hole));
  });
}

function getPointsInsideBoundaries(points, provinceLayers) {
  return points.flatMap(point => {
    const latitude = Number(point.latitude);
    const longitude = Number(point.longitude);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
      return [];
    }

    const location = L.latLng(latitude, longitude);
    const province = provinceLayers.find(layer => (
      layer.getBounds().contains(location) && geometryContainsPoint(layer.feature.geometry, latitude, longitude)
    ));

    return province ? [{ point, latitude, longitude, location }] : [];
  });
}

function initializeHomePageTwoMap() {
  const mapElement = document.getElementById('synexHome2Map');

  if (!mapElement) {
    return;
  }

  const data = window.synexHome2MapData;
  const status = document.getElementById('synexHome2MapStatus');
  const pointCount = document.getElementById('home2-map-point-count');

  if (
    !data ||
    !Array.isArray(data.points) ||
    !data.provinceEventCounts ||
    !data.provinceEventTypeCounts ||
    !data.eventTypeColors ||
    !data.geoJsonUrl
  ) {
    throw new Error('Home page 2 map data is missing or invalid.');
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
      const eventTypeCounts = new Map(
        Object.entries(data.provinceEventTypeCounts).map(([province, counts]) => [normalizeProvinceName(province), counts])
      );
      const eventTypeColor = type => {
        const label = normalizeEventType(type);
        return Object.prototype.hasOwnProperty.call(data.eventTypeColors, label)
          ? data.eventTypeColors[label]
          : '#AF8807';
      };
      const countForFeature = feature => eventCounts.get(normalizeProvinceName(feature.properties.shapeName)) || 0;
      const colorForCount = count => {
        if (count >= 50) return '#806300';
        if (count >= 20) return '#AF8807';
        if (count >= 10) return '#D2AD36';
        if (count > 0) return '#E8DCA9';
        return '#F7F5EF';
      };

      const map = L.map(mapElement, {
        maxBoundsViscosity: 1,
        maxZoom: 11,
        scrollWheelZoom: false
      });
      const provinces = L.geoJSON(boundaries, {
        attribution:
          '<a href="https://www.geoboundaries.org/">GeoBoundaries</a> (OpenStreetMap/Wambacher, ODbL 1.0)',
        style: feature => ({
          color: '#FFFFFF',
          weight: 1.2,
          opacity: 1,
          fillColor: colorForCount(countForFeature(feature)),
          fillOpacity: countForFeature(feature) === 0 ? 0.45 : 0.72
        }),
        onEachFeature: (feature, layer) => {
          const provinceName = feature.properties.shapeName;
          const count = countForFeature(feature);
          const tooltip = document.createElement('span');
          tooltip.textContent = `${provinceName} : ${new Intl.NumberFormat('fr-FR').format(count)} événement${count > 1 ? 's' : ''}`;
          layer.bindTooltip(tooltip);
          layer.bindPopup(
            createMapPopup(
              provinceName,
              count,
              eventTypeCounts.get(normalizeProvinceName(provinceName)),
              data.eventTypeColors
            ),
            { maxWidth: 300 }
          );
          layer.on({
            mouseover: () => layer.setStyle({ weight: 2, color: '#806300', fillOpacity: 0.88 }),
            mouseout: () => provinces.resetStyle(layer)
          });
        }
      }).addTo(map);

      const bounds = provinces.getBounds();
      const mapPadding = L.point(18, 18);
      map.setMinZoom(map.getBoundsZoom(bounds, false, mapPadding));
      map.fitBounds(bounds, { padding: mapPadding });
      map.setMaxBounds(bounds.pad(0.05));

      const provinceLayers = [];
      provinces.eachLayer(layer => provinceLayers.push(layer));
      const locatedPoints = getPointsInsideBoundaries(data.points, provinceLayers);
      const heroMapElement = document.getElementById('synexHome2HeroMap');

      if (heroMapElement) {
        const heroMap = L.map(heroMapElement, {
          attributionControl: false,
          boxZoom: false,
          dragging: false,
          doubleClickZoom: false,
          keyboard: false,
          scrollWheelZoom: false,
          tap: false,
          touchZoom: false,
          zoomControl: false
        });
        const heroProvinces = L.geoJSON(boundaries, {
          style: {
            color: '#FFFFFF',
            weight: 1.2,
            fillColor: '#D2AD36',
            fillOpacity: 0.38
          }
        }).addTo(heroMap);
        locatedPoints.forEach(({ point, location }) => {
          L.circleMarker(location, {
            radius: 3.5,
            color: '#FFFFFF',
            weight: 1.25,
            fillColor: eventTypeColor(point.eventType),
            fillOpacity: 1
          }).addTo(heroMap);
        });
        heroMap.fitBounds(heroProvinces.getBounds(), { padding: [8, 8] });
      }

      const markers = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 28,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: false,
        iconCreateFunction: cluster => {
          const label = document.createElement('span');
          label.textContent = new Intl.NumberFormat('fr-FR').format(cluster.getChildCount());
          return L.divIcon({
            html: label,
            className: 'synex-home2-marker-cluster',
            iconSize: L.point(34, 34)
          });
        }
      });

      locatedPoints.forEach(({ point, latitude, longitude, location }) => {
        const type = normalizeEventType(point.eventType);
        const marker = L.circleMarker(location, {
          radius: 5,
          color: '#FFFFFF',
          weight: 1.5,
          fillColor: eventTypeColor(type),
          fillOpacity: 0.95
        });
        marker.synexEventType = type;
        const popup = document.createElement('div');
        popup.className = 'synex-home2-map-popup';
        const title = document.createElement('strong');
        title.className = 'synex-home2-map-popup-title';
        title.textContent = point.date || 'Événement';
        popup.appendChild(title);
        const eventTotal = document.createElement('p');
        eventTotal.className = 'synex-home2-map-popup-total';
        eventTotal.textContent = 'Nombre d’événements : 1';
        popup.appendChild(eventTotal);

        [
          ['Type d’événement', type],
          ['Province', point.province],
          ['Coordonnées', `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`],
          ['Décès', point.deaths],
          ['Blessés', point.injuries]
        ].forEach(([label, value]) => {
          if (value === null || value === undefined || value === '') {
            return;
          }

          const detail = document.createElement('p');
          detail.className = 'mb-1';
          detail.textContent = `${label} : ${value}`;
          popup.appendChild(detail);
        });

        marker.bindPopup(popup);
        markers.addLayer(marker);
      });

      map.addLayer(markers);
      markers.on('clusterclick', event => {
        const clusterMarkers = event.layer.getAllChildMarkers();
        const countsByType = clusterMarkers.reduce((counts, marker) => {
          counts[marker.synexEventType] = (counts[marker.synexEventType] || 0) + 1;
          return counts;
        }, {});
        L.popup({ maxWidth: 300 })
          .setLatLng(event.layer.getBounds().getCenter())
          .setContent(createMapPopup('Groupe d’incidents', clusterMarkers.length, countsByType, data.eventTypeColors))
          .openOn(map);
      });

      if (pointCount) {
        pointCount.textContent = `${new Intl.NumberFormat('fr-FR').format(locatedPoints.length)} points GPS`;
      }

      if (status) {
        status.textContent = `${boundaries.features.length} provinces affichées · ${new Intl.NumberFormat('fr-FR').format(locatedPoints.length)} événements positionnés à partir des coordonnées enregistrées.`;
      }
    })
    .catch(error => {
      console.error('Unable to render the SYNEX Chad map.', error);
      if (status) {
        status.textContent = 'La carte du Tchad ne peut pas être chargée pour le moment. Veuillez réessayer plus tard.';
      }
    });
}

function initializeHomePageTwoCarousels() {
  document.querySelectorAll('[data-home2-carousel]').forEach(carousel => {
    const track = carousel.querySelector('[data-home2-track]');
    const previous = carousel.querySelector('[data-home2-prev]');
    const next = carousel.querySelector('[data-home2-next]');
    const dots = carousel.querySelector('[data-home2-dots]');
    const cards = track ? Array.from(track.children) : [];

    if (!track || !previous || !next || !dots || cards.length === 0) {
      return;
    }

    let index = 0;

    const visibleCount = () => {
      const count = Number.parseInt(getComputedStyle(track).getPropertyValue('--home2-visible-cards'), 10);
      return Number.isFinite(count) && count > 0 ? count : 1;
    };

    const maxIndex = () => Math.max(0, cards.length - visibleCount());

    const update = () => {
      index = Math.min(index, maxIndex());
      const cardWidth = cards[0].getBoundingClientRect().width;
      const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
      track.style.transform = `translateX(-${index * (cardWidth + gap)}px)`;

      previous.disabled = index === 0;
      next.disabled = index >= maxIndex();
      Array.from(dots.children).forEach((dot, dotIndex) => {
        if (dotIndex === index) {
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.removeAttribute('aria-current');
        }
      });
    };

    const buildDots = () => {
      dots.replaceChildren();
      for (let dotIndex = 0; dotIndex <= maxIndex(); dotIndex += 1) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', `Afficher les actualités à partir de la carte ${dotIndex + 1}`);
        dot.addEventListener('click', () => {
          index = dotIndex;
          update();
        });
        dots.appendChild(dot);
      }

      const hasOverflow = maxIndex() > 0;
      previous.hidden = !hasOverflow;
      next.hidden = !hasOverflow;
      dots.hidden = !hasOverflow;
    };

    previous.addEventListener('click', () => {
      index = Math.max(0, index - 1);
      update();
    });

    next.addEventListener('click', () => {
      index = Math.min(maxIndex(), index + 1);
      update();
    });

    let resizeFrame;
    window.addEventListener('resize', () => {
      window.cancelAnimationFrame(resizeFrame);
      resizeFrame = window.requestAnimationFrame(() => {
        buildDots();
        update();
      });
    });

    buildDots();
    update();
  });
}

if (document.readyState === 'loading') {
  document.addEventListener(
    'DOMContentLoaded',
    () => {
      initializeHomePageTwoCarousels();
      initializeHomePageTwoMap();
    },
    { once: true }
  );
} else {
  initializeHomePageTwoCarousels();
  initializeHomePageTwoMap();
}
