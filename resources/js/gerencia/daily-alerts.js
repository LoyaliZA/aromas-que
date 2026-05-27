const STORAGE_KEY = 'gerencia_daily_alerts_enabled';
const SOUND_ENABLED_KEY = 'gerencia_daily_sound_enabled';
const VOICE_ENABLED_KEY = 'gerencia_daily_voice_enabled';
const POLL_MS = 15000;
const SUMMARY_MS = 300000;

const DailyAlerts = {
    knownPendingIds: new Set(),
    statsInitialized: false,
    lastRezagadosCount: null,
    soundEnabled: true,
    voiceEnabled: true,
    activated: false,
    audio: null,
    pollTimer: null,
    summaryTimer: null,
    preferredSpanishVoice: null,
    voiceReady: false,

    init() {
        this.soundEnabled = localStorage.getItem(SOUND_ENABLED_KEY) !== 'false';
        this.voiceEnabled = localStorage.getItem(VOICE_ENABLED_KEY) !== 'false';
        this.activated = localStorage.getItem(STORAGE_KEY) === 'true';

        this.audio = new Audio('/sounds/nuevo_resguardo.mp3');
        this.audio.volume = 0.85;
        this.audio.addEventListener('error', () => {
            this.audio = null;
        });

        window.dailyAlertsBridge = {
            shouldPause: () => this.isPaused(),
            onStatsUpdate: (counts) => {
                window.dispatchEvent(new CustomEvent('daily-stats-update', { detail: counts }));
            },
        };

        this.prepareVoices();

        if (this.activated) {
            this.hideActivationBanner();
            this.startTimers();
        }
    },

    prepareVoices() {
        if (!('speechSynthesis' in window)) return;

        const load = () => {
            const voice = this.selectBestSpanishVoice(speechSynthesis.getVoices());
            if (voice) {
                this.preferredSpanishVoice = voice;
                this.voiceReady = true;
            }
        };

        load();
        speechSynthesis.addEventListener('voiceschanged', load);
        setTimeout(load, 300);
        setTimeout(load, 1000);
    },

    selectBestSpanishVoice(voices) {
        const spanish = voices.filter((v) => v.lang && v.lang.toLowerCase().startsWith('es'));
        if (!spanish.length) return null;

        const scoreVoice = (v) => {
            let score = 0;
            const name = (v.name || '').toLowerCase();
            const lang = (v.lang || '').toLowerCase();

            if (lang.includes('es-mx')) score += 45;
            else if (lang.includes('es-us')) score += 40;
            else if (lang.startsWith('es')) score += 25;

            if (/natural|neural|premium|enhanced|online/i.test(name)) score += 55;
            if (/google/i.test(name)) score += 50;
            if (/microsoft/i.test(name) && /natural|sabina|helena|laura|raul|raúl|dalia|jorge/i.test(name)) {
                score += 48;
            }
            if (/sabina|helena|laura|monica|mónica|paulina|jorge|diego|mexico|méxico/i.test(name)) {
                score += 35;
            }

            if (!v.localService) score += 12;

            if (/espeak|mbrola|compact|robot|android.*speech|system\s*voice/i.test(name)) {
                score -= 40;
            }
            if (v.default && !/google|microsoft|natural|neural/i.test(name)) score -= 15;

            return score;
        };

        return [...spanish].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0];
    },

    isPaused() {
        const bridge = window.dailyAlertsBridge?._alpine;
        if (window.dailyAlertsBridge?.deliveryModalOpen) return true;
        if (!bridge) return false;
        if (typeof bridge.isRefreshPaused === 'function') {
            return bridge.isRefreshPaused();
        }
        return (
            bridge.showEditModal ||
            bridge.showDetailsModal ||
            bridge.showDeleteModal ||
            bridge.showRejectModal ||
            bridge.showBulkDeleteModal ||
            bridge.showImageViewer ||
            bridge.selectedPickups?.length > 0
        );
    },

    bindAlpine(component) {
        window.dailyAlertsBridge._alpine = component;
    },

    activate() {
        this.activated = true;
        localStorage.setItem(STORAGE_KEY, 'true');
        this.hideActivationBanner();
        this.unlockAudio();
        this.startTimers();
    },

    hideActivationBanner() {
        const banner = document.getElementById('daily-alerts-activation');
        if (banner) banner.classList.add('hidden');
    },

    unlockAudio() {
        if (this.audio) {
            this.audio.play().then(() => {
                this.audio.pause();
                this.audio.currentTime = 0;
            }).catch(() => {});
        }
        if ('speechSynthesis' in window) {
            const u = new SpeechSynthesisUtterance('');
            u.volume = 0;
            speechSynthesis.speak(u);
            speechSynthesis.cancel();
        }
    },

    startTimers() {
        this.stopTimers();
        this.fetchStats();
        // La tabla en daily.blade ya hace refresh + fetchStats cada 15s; aquí solo el resumen de voz.
        this.summaryTimer = setInterval(() => this.speakPeriodicSummary(), SUMMARY_MS);
    },

    stopTimers() {
        if (this.pollTimer) clearInterval(this.pollTimer);
        if (this.summaryTimer) clearInterval(this.summaryTimer);
    },

    getFilterParams() {
        const bridge = window.dailyAlertsBridge?._alpine;
        if (!bridge) return new URLSearchParams();
        return new URLSearchParams({
            search: bridge.search || '',
            status: bridge.status || 'ALL',
            department: bridge.department || 'ALL',
        });
    },

    async fetchStats() {
        if (this.isPaused()) return;

        try {
            const params = this.getFilterParams();
            const response = await fetch(`/gerencia/daily/stats?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
            if (!response.ok) return;

            const data = await response.json();
            this.processStats(data);
        } catch (e) {
            console.error('daily stats poll', e);
        }
    },

    /** @deprecated alias */
    pollStats() {
        return this.fetchStats();
    },

    processStats(data) {
        const counts = data.counts || {};
        window.dailyAlertsBridge?.onStatsUpdate?.({
            ...counts,
            widgets: data.widgets || {},
        });

        if (!this.activated || this.isPaused()) return;

        const pendingList = data.pending || [];

        if (!this.statsInitialized) {
            pendingList.forEach((p) => this.knownPendingIds.add(String(p.id)));
            this.statsInitialized = true;
        } else {
            const newItems = pendingList.filter((p) => !this.knownPendingIds.has(String(p.id)));
            pendingList.forEach((p) => this.knownPendingIds.add(String(p.id)));
            newItems.forEach((item) => {
                this.playSound();
                this.speak(`Nuevo resguardo pendiente de aprobación, folio ${item.folio}`);
            });
        }

        if (data.can_manage_rezagados && this.lastRezagadosCount !== null) {
            if (counts.rezagados > this.lastRezagadosCount) {
                this.playSound();
                this.speak('Hay un nuevo resguardo en bóveda de rezagados');
            }
        }
        if (data.can_manage_rezagados) {
            this.lastRezagadosCount = counts.rezagados;
        }
    },

    async speakPeriodicSummary() {
        if (!this.activated || !this.voiceEnabled || this.isPaused()) return;

        try {
            const params = this.getFilterParams();
            const response = await fetch(`/gerencia/daily/stats?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
            if (!response.ok) return;

            const data = await response.json();
            const counts = data.counts || {};
            const parts = [];

            if (counts.pending > 0) {
                parts.push(
                    `Tienes ${counts.pending} resguardo${counts.pending === 1 ? '' : 's'} por aprobar`
                );
            }
            if (counts.stale_pending > 0) {
                parts.push(
                    `Hay ${counts.stale_pending} resguardo${counts.stale_pending === 1 ? '' : 's'} pendientes de días anteriores`
                );
            }

            if (parts.length === 0) return;

            this.speak(parts.join('. '));
        } catch (e) {
            console.error('daily summary', e);
        }
    },

    playSound() {
        if (!this.soundEnabled) return;
        if (this.audio) {
            this.audio.currentTime = 0;
            this.audio.play().catch(() => this.playBeep());
        } else {
            this.playBeep();
        }
    },

    playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.15;
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } catch (_) {}
    },

    speak(text) {
        if (!this.voiceEnabled || !('speechSynthesis' in window)) return;

        if (!this.preferredSpanishVoice) {
            this.preferredSpanishVoice = this.selectBestSpanishVoice(speechSynthesis.getVoices());
        }

        speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        const voice = this.preferredSpanishVoice;

        utterance.lang = voice?.lang || 'es-MX';
        utterance.rate = 0.88;
        utterance.pitch = 1;
        utterance.volume = 1;

        if (voice) {
            utterance.voice = voice;
        }

        speechSynthesis.speak(utterance);
    },

    setSoundEnabled(enabled) {
        this.soundEnabled = enabled;
        localStorage.setItem(SOUND_ENABLED_KEY, enabled ? 'true' : 'false');
    },

    setVoiceEnabled(enabled) {
        this.voiceEnabled = enabled;
        localStorage.setItem(VOICE_ENABLED_KEY, enabled ? 'true' : 'false');
    },

    async test() {
        this.unlockAudio();

        this.playSound();

        let message =
            'Prueba de alertas. Nuevo resguardo pendiente de aprobación, folio de prueba.';

        try {
            const params = this.getFilterParams();
            const response = await fetch(`/gerencia/daily/stats?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                const counts = data.counts || {};
                const parts = [
                    'Prueba de alertas.',
                    'Nuevo resguardo pendiente de aprobación, folio de prueba.',
                ];

                if (counts.pending > 0) {
                    parts.push(
                        `Tienes ${counts.pending} resguardo${counts.pending === 1 ? '' : 's'} por aprobar.`
                    );
                }
                if (counts.stale_pending > 0) {
                    parts.push(
                        `Hay ${counts.stale_pending} pendiente${counts.stale_pending === 1 ? '' : 's'} de días anteriores.`
                    );
                }
                if (data.can_manage_rezagados && counts.rezagados > 0) {
                    parts.push(
                        `Hay ${counts.rezagados} resguardo${counts.rezagados === 1 ? '' : 's'} en bóveda de rezagados.`
                    );
                }

                message = parts.join(' ');
                window.dailyAlertsBridge?.onStatsUpdate?.(counts);
            }
        } catch (e) {
            console.error('daily alerts test', e);
        }

        setTimeout(() => this.speak(message), 450);
    },
};

document.addEventListener('DOMContentLoaded', () => DailyAlerts.init());

window.DailyAlerts = DailyAlerts;
