import { initUi } from './modules/ui';
import { initMap } from './modules/map';
import { initCharts } from './modules/charts';

initUi();

// Data halaman dikirim server lewat <script id="page-data" type="application/json">
const payloadEl = document.getElementById('page-data');
if (payloadEl) {
    const payload = JSON.parse(payloadEl.textContent);
    if (payload.points) initMap(document.getElementById('map'), payload);
    if (payload.charts) initCharts(payload.charts);
}
