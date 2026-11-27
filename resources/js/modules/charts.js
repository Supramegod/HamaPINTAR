import Chart from 'chart.js/auto';

const COLORS = {
    red: '#ef4444',
    blue: '#3b82f6',
    amber: '#d97706',
    slate: '#64748b',
    grid: '#e2e8f0',
};

Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
Chart.defaults.color = COLORS.slate;

/** Opsi dasar; dibuat ulang per chart karena Chart.js memodifikasi objek options. */
const baseOptions = ({ legend = false } = {}) => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: legend, position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', pointStyleWidth: 8, boxHeight: 6, padding: 16 } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: {
            beginAtZero: true,
            grid: { color: COLORS.grid },
            border: { dash: [5, 5] },
            ticks: { font: { size: 11 }, precision: 0 },
        },
    },
});

const builders = {
    hour: ({ labels, values }) => ({
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Deteksi',
                data: values,
                borderColor: COLORS.red,
                backgroundColor: 'rgba(239, 68, 68, 0.08)',
                fill: true,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: COLORS.red,
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
            }],
        },
        options: baseOptions(),
    }),

    day: ({ labels, values }) => ({
        type: 'bar',
        data: { labels, datasets: [{ label: 'Deteksi', data: values, backgroundColor: COLORS.blue, borderRadius: 6, maxBarThickness: 36 }] },
        options: baseOptions(),
    }),

    prediction: ({ labels, actual, forecast }) => ({
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Aktual',
                    data: actual,
                    borderColor: COLORS.slate,
                    borderWidth: 2,
                    pointBackgroundColor: COLORS.slate,
                    pointRadius: 3,
                    tension: 0.2,
                },
                {
                    label: 'Prediksi',
                    data: forecast,
                    borderColor: COLORS.amber,
                    borderWidth: 3,
                    borderDash: [6, 6],
                    backgroundColor: 'rgba(217, 119, 6, 0.08)',
                    fill: true,
                    pointBackgroundColor: COLORS.amber,
                    pointRadius: 5,
                    tension: 0.2,
                },
            ],
        },
        options: baseOptions({ legend: true }),
    }),
};

/** @param {Record<string, object>} charts payload per chart, kunci = atribut data-chart */
export function initCharts(charts) {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const key = canvas.dataset.chart;
        if (builders[key] && charts[key]) {
            new Chart(canvas, builders[key](charts[key]));
        }
    });
}
