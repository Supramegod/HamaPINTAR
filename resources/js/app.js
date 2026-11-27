import L from 'leaflet';
import Chart from 'chart.js/auto';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Leaflet mencari ikon marker lewat path relatif CSS; arahkan ke aset hasil bundle Vite.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Tombol tutup notifikasi (pengganti data-bs-dismiss Bootstrap)
document.querySelectorAll('[data-dismiss-alert]').forEach((btn) => {
    btn.addEventListener('click', () => btn.closest('[data-alert]')?.remove());
});

const dataEl = document.getElementById('dashboard-data');
if (dataEl) {
    const data = JSON.parse(dataEl.textContent);
    initMap(data.logs);
    initCharts(data);
}

function initMap(logsData) {
    const map = L.map('map', { zoomControl: false }).setView([-7.252, 112.7944], 17);
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (logsData.length > 0) {
        const escape = (s) => String(s).replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`);
        const markers = logsData.map((log) =>
            L.marker([log.latitude, log.longitude])
                .bindPopup(`<b>${escape(log.node_name)}</b><br>Waktu: ${escape(log.detected_at)}`)
                .addTo(map),
        );
        map.fitBounds(L.featureGroup(markers).getBounds());
    }
}

function initCharts(data) {
    // Factory: Chart.js memodifikasi objek options, jadi tiap chart butuh salinan sendiri
    const cleanChartOptions = () => ({
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' } },
            y: { grid: { color: '#e2e8f0' }, ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }, border: { dash: [5, 5] } },
        },
    });

    // 1. Grafik Harian
    new Chart(document.getElementById('chartJam'), {
        type: 'line',
        data: {
            labels: ['18:00', '20:00', '22:00', '00:00', '02:00', '04:00', '06:00'],
            datasets: [{ data: data.chartJamData, borderColor: '#ef4444', borderWidth: 2, pointBackgroundColor: '#ffffff', pointBorderColor: '#ef4444', pointBorderWidth: 2, tension: 0.4 }],
        },
        options: cleanChartOptions(),
    });

    // 2. Grafik Mingguan
    new Chart(document.getElementById('chartHari'), {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{ data: data.chartHariData, backgroundColor: '#3b82f6', borderRadius: 6, barThickness: 16 }],
        },
        options: cleanChartOptions(),
    });

    // 3. Grafik Kerawanan PIR
    new Chart(document.getElementById('chartNode'), {
        type: 'bar',
        data: {
            labels: ['PIR 1', 'PIR 2', 'PIR 3', 'PIR 4', 'PIR 5'],
            datasets: [{ data: data.chartNodeData, backgroundColor: '#10b981', borderRadius: 6, barThickness: 16 }],
        },
        options: cleanChartOptions(),
    });

    // 4. Grafik Prediksi Kedepan (garis putus-putus = nilai proyeksi)
    new Chart(document.getElementById('chartPrediksi'), {
        type: 'line',
        data: {
            labels: data.predictiveLabels,
            datasets: [{
                data: data.predictiveValues,
                borderColor: '#d97706',
                borderWidth: 3,
                borderDash: [5, 5],
                backgroundColor: 'rgba(217, 119, 6, 0.05)',
                fill: true,
                pointBackgroundColor: '#d97706',
                pointRadius: 5,
                tension: 0.2,
            }],
        },
        options: cleanChartOptions(),
    });
}
