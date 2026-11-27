import L from 'leaflet';

const escapeHtml = (value) =>
    String(value).replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);

/**
 * Peta sebaran deteksi. Tiap node digambar sebagai titik berwarna sesuai legenda.
 *
 * @param {HTMLElement|null} el
 * @param {{ map: {center: number[], zoom: number}, nodes: Record<string,string>, points: {node:string,lat:number,lng:number,at:string}[] }} payload
 */
export function initMap(el, { map: config, nodes, points }) {
    if (!el) return;

    const map = L.map(el, { zoomControl: false }).setView(config.center, config.zoom);
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (points.length === 0) return;

    const markers = points.map((point) =>
        L.circleMarker([point.lat, point.lng], {
            radius: 8,
            color: '#ffffff',
            weight: 2,
            fillColor: nodes[point.node] ?? '#64748b',
            fillOpacity: 0.9,
        }).bindPopup(`<b>${escapeHtml(point.node)}</b><br>Waktu: ${escapeHtml(point.at)}`),
    );

    const group = L.featureGroup(markers).addTo(map);
    map.fitBounds(group.getBounds(), { padding: [40, 40], maxZoom: 18 });
}
