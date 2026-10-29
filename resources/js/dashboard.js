import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip);

const REFRESH_MS = 20000;

/**
 * Dashboard: memuat grafik tren & aktivitas, lalu melakukan polling ringan.
 * Polling dihentikan saat tab tidak terlihat (hemat baterai & beban server).
 */
export function registerDashboard() {
    return {
        loading: true,
        error: null,
        period: 'week',
        trend: [],
        recent: [],
        lowStock: [],
        chart: null,
        timer: null,
        lastUpdated: null,

        init() {
            this.fetchFeed();
            this.timer = setInterval(() => {
                if (!document.hidden) this.fetchFeed();
            }, REFRESH_MS);
        },

        destroy() {
            clearInterval(this.timer);
            this.chart?.destroy();
        },

        async fetchFeed() {
            try {
                const { data } = await window.axios.get('/dashboard/feed', { params: { period: this.period } });
                this.trend = data.trend;
                this.recent = data.recent;
                this.lowStock = data.low_stock;
                this.lastUpdated = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                this.error = null;
                this.renderChart(data.trend);
            } catch {
                this.error = 'Data terbaru belum bisa dimuat. Menampilkan data terakhir.';
            } finally {
                this.loading = false;
            }
        },

        changePeriod(period) {
            if (this.period === period) return;
            this.period = period;
            this.loading = true;
            this.fetchFeed();
        },

        renderChart(trend) {
            const canvas = this.$refs.trend;
            if (!canvas || !trend) return;

            const grid = 'rgba(100,116,139,0.12)';
            const text = '#475569';

            const data = {
                labels: trend.map((d) => d.label),
                datasets: [{
                    label: 'Pendapatan',
                    data: trend.map((d) => d.revenue),
                    borderColor: '#0d9488',
                    backgroundColor: (ctx) => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                        g.addColorStop(0, 'rgba(20,184,166,0.35)');
                        g.addColorStop(1, 'rgba(20,184,166,0)');
                        return g;
                    },
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d9488',
                    pointHoverRadius: 7,
                }],
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 700, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.9)',
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: { label: (c) => window.rupiah(c.parsed.y) },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: text } },
                    y: {
                        grid: { color: grid },
                        border: { display: false },
                        ticks: { color: text, callback: (v) => (v >= 1000 ? `${v / 1000}rb` : v) },
                        beginAtZero: true,
                    },
                },
            };

            if (this.chart) {
                this.chart.data = data;
                this.chart.options = options;
                this.chart.update();
            } else {
                this.chart = new Chart(canvas, { type: 'line', data, options });
            }
        },
    };
}
