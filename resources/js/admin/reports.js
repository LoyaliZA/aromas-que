document.addEventListener('alpine:init', () => {
    // Registramos el componente de forma nativa en Alpine
    window.Alpine.data('reportsDashboard', () => ({
        activeTab: window.ReportConfig ? window.ReportConfig.activeTab : 'realtime',
        isLoading: false,
        realTimeData: [],
        pollingInterval: null,
        clockInterval: null,
        currentTime: Date.now(),

        initDashboard() {
            if (this.activeTab === 'realtime') {
                this.startRealTime();
            }
            this.initCharts(); // Inicializamos las gráficas
        },

        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState(null, '', url.toString());

            if (tab === 'realtime') {
                this.startRealTime();
            } else {
                this.stopRealTime();
            }
        },

        startRealTime() {
            this.fetchRealTimeData(); 
            this.pollingInterval = setInterval(() => {
                this.fetchRealTimeData();
            }, 5000);

            this.clockInterval = setInterval(() => {
                this.currentTime = Date.now();
            }, 1000);
        },

        stopRealTime() {
            clearInterval(this.pollingInterval);
            clearInterval(this.clockInterval);
        },

        fetchRealTimeData() {
            if (!window.ReportConfig || !window.ReportConfig.realtimeRoute) return;

            fetch(window.ReportConfig.realtimeRoute, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.realTimeData = data.sellers;
            })
            .catch(err => console.error("Error obteniendo datos", err));
        },

        formatTimer(startedAt) {
            if (!startedAt) return "00:00";
            let elapsedSecs = Math.floor((this.currentTime - startedAt) / 1000);
            if (elapsedSecs < 0) elapsedSecs = 0;

            let hrs = Math.floor(elapsedSecs / 3600);
            let mins = Math.floor((elapsedSecs % 3600) / 60);
            let secs = Math.floor(elapsedSecs % 60);
            
            let result = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            if (hrs > 0) result = `${hrs.toString().padStart(2, '0')}:` + result;
            
            return result;
        },

        initCharts() {
            if (typeof Chart !== 'undefined' && window.ReportConfig && window.ReportConfig.chartData) {
                Chart.defaults.color = '#9ca3af';
                Chart.defaults.borderColor = 'rgba(75, 85, 99, 0.2)';
                Chart.defaults.font.family = "'Figtree', sans-serif";

                const ctxGlobal = document.getElementById('salesHourlyChart');
                // Evita crear la gráfica dos veces si Alpine se reinicia
                if (ctxGlobal && !window.salesChartInstance) { 
                    window.salesChartInstance = new Chart(ctxGlobal, {
                        type: 'line',
                        data: {
                            labels: window.ReportConfig.chartData.labels,
                            datasets: [{
                                label: 'Turnos Atendidos',
                                data: window.ReportConfig.chartData.data,
                                borderColor: '#3b82f6', 
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#FDC974'
                            }]
                        },
                        options: { 
                            responsive: true, maintainAspectRatio: false, 
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                        }
                    });
                }
            }
        }
    }));
});