const STORAGE_KEY = 'gerencia_daily_alerts_enabled';
const SOUND_ENABLED_KEY = 'gerencia_daily_sound_enabled';
const VOICE_ENABLED_KEY = 'gerencia_daily_voice_enabled';
const VOICE_URI_KEY = 'gerencia_daily_voice_uri';
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
    activeSoundUrl: null,
    pollTimer: null,
    summaryTimer: null,
    preferredSpanishVoice: null,
    voiceReady: false,
    speechQueue: [],
    isSpeaking: false,

    init() {
        this.soundEnabled = localStorage.getItem(SOUND_ENABLED_KEY) !== 'false';
        this.voiceEnabled = localStorage.getItem(VOICE_ENABLED_KEY) !== 'false';
        this.activated = localStorage.getItem(STORAGE_KEY) === 'true';

        this.initAudio();

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

        window.dispatchEvent(new CustomEvent('daily-alerts-ready'));
    },

    getSoundUrls() {
        const cfg = window.__dailyAlertsConfig?.soundUrls;
        if (Array.isArray(cfg) && cfg.length > 0) return cfg;
        return ['/sounds/nuevo_resguardo.mp3', '/audio/nuevo_resguardo.mp3'];
    },

    initAudio() {
        const urls = this.getSoundUrls();
        this.tryLoadSound(urls, 0);
    },

    tryLoadSound(urls, index) {
        if (index >= urls.length) {
            console.warn('[DailyAlerts] No se pudo cargar el sonido de alerta:', urls);
            return;
        }

        const url = urls[index];
        const audio = new Audio(url);
        audio.preload = 'auto';
        audio.volume = 0.9;

        const onReady = () => {
            this.audio = audio;
            this.activeSoundUrl = url;
        };

        const onFail = () => {
            audio.removeEventListener('canplaythrough', onReady);
            audio.removeEventListener('error', onFail);
            this.tryLoadSound(urls, index + 1);
        };

        audio.addEventListener('canplaythrough', onReady, { once: true });
        audio.addEventListener('error', onFail, { once: true });
        audio.load();
    },

    prepareVoices() {
        if (!('speechSynthesis' in window)) return;

        const load = () => {
            const savedUri = localStorage.getItem(VOICE_URI_KEY);
            const voices = speechSynthesis.getVoices();
            if (savedUri) {
                const saved = voices.find((v) => v.voiceURI === savedUri);
                if (saved) {
                    this.preferredSpanishVoice = saved;
                    this.voiceReady = true;
                    return;
                }
            }
            const best = this.selectBestSpanishVoice(voices);
            if (best) {
                this.preferredSpanishVoice = best;
                this.voiceReady = true;
            }
        };

        load();
        speechSynthesis.addEventListener('voiceschanged', load);
        setTimeout(load, 200);
        setTimeout(load, 800);
        setTimeout(load, 2000);
    },

    getSpanishVoices() {
        return speechSynthesis.getVoices().filter((v) => v.lang && v.lang.toLowerCase().startsWith('es'));
    },

    getVoiceOptions() {
        return this.getSpanishVoices()
            .map((v) => ({
                uri: v.voiceURI,
                label: `${v.name} (${v.lang})`,
                score: this.scoreVoice(v),
            }))
            .sort((a, b) => b.score - a.score);
    },

    setPreferredVoiceUri(uri) {
        if (!uri) {
            localStorage.removeItem(VOICE_URI_KEY);
            this.preferredSpanishVoice = this.selectBestSpanishVoice(speechSynthesis.getVoices());
            return;
        }
        const voice = speechSynthesis.getVoices().find((v) => v.voiceURI === uri);
        if (voice) {
            localStorage.setItem(VOICE_URI_KEY, uri);
            this.preferredSpanishVoice = voice;
        }
    },

    selectBestSpanishVoice(voices) {
        const spanish = voices.filter((v) => v.lang && v.lang.toLowerCase().startsWith('es'));
        if (!spanish.length) return null;
        return [...spanish].sort((a, b) => this.scoreVoice(b) - this.scoreVoice(a))[0];
    },

    scoreVoice(v) {
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
        if (/sabina|helena|laura|monica|mónica|paulina|jorge|diego/i.test(name)) {
            score += 35;
        }

        if (!v.localService) score += 12;

        if (/espeak|mbrola|compact|robot|android.*speech/i.test(name)) score -= 40;
        if (v.default && !/google|microsoft|natural|neural/i.test(name)) score -= 15;

        return score;
    },

    isPaused() {
        if (window.dailyAlertsBridge?.deliveryModalOpen) return true;
        const bridge = window.dailyAlertsBridge?._alpine;
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
            const clone = this.audio.cloneNode();
            clone.volume = 0.01;
            clone.play().then(() => clone.pause()).catch(() => {});
        }
        if ('speechSynthesis' in window) {
            speechSynthesis.cancel();
        }
    },

    startTimers() {
        this.stopTimers();
        this.fetchStats();
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

        if (!this.audio) {
            this.initAudio();
            setTimeout(() => this.playSound(), 400);
            return;
        }

        const sound = this.audio;
        sound.currentTime = 0;
        sound.play().catch((err) => {
            console.warn('[DailyAlerts] Error al reproducir:', this.activeSoundUrl, err);
            this.initAudio();
        });
    },

    enqueueSpeech(text) {
        this.speechQueue.push(text);
        this.processSpeechQueue();
    },

    processSpeechQueue() {
        if (this.isSpeaking || !this.speechQueue.length) return;
        if (!this.voiceEnabled || !('speechSynthesis' in window)) {
            this.speechQueue = [];
            return;
        }

        const text = this.speechQueue.shift();
        this.isSpeaking = true;

        const voices = speechSynthesis.getVoices();
        if (!this.preferredSpanishVoice && voices.length) {
            const savedUri = localStorage.getItem(VOICE_URI_KEY);
            this.preferredSpanishVoice = savedUri
                ? voices.find((v) => v.voiceURI === savedUri) || this.selectBestSpanishVoice(voices)
                : this.selectBestSpanishVoice(voices);
        }

        const utterance = new SpeechSynthesisUtterance(text);
        const voice = this.preferredSpanishVoice;

        utterance.lang = voice?.lang || 'es-MX';
        utterance.rate = 0.92;
        utterance.pitch = 1;
        utterance.volume = 1;
        if (voice) utterance.voice = voice;

        utterance.onend = () => {
            this.isSpeaking = false;
            this.processSpeechQueue();
        };
        utterance.onerror = () => {
            this.isSpeaking = false;
            this.processSpeechQueue();
        };

        speechSynthesis.speak(utterance);
    },

    speak(text) {
        if (!this.voiceEnabled || !('speechSynthesis' in window)) return;
        this.enqueueSpeech(text);
    },

    setSoundEnabled(enabled) {
        this.soundEnabled = enabled;
        localStorage.setItem(SOUND_ENABLED_KEY, enabled ? 'true' : 'false');
    },

    setVoiceEnabled(enabled) {
        this.voiceEnabled = enabled;
        localStorage.setItem(VOICE_ENABLED_KEY, enabled ? 'true' : 'false');
        if (!enabled) {
            speechSynthesis.cancel();
            this.speechQueue = [];
            this.isSpeaking = false;
        }
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
                window.dailyAlertsBridge?.onStatsUpdate?.({
                    ...counts,
                    widgets: data.widgets || {},
                });
            }
        } catch (e) {
            console.error('daily alerts test', e);
        }

        setTimeout(() => this.speak(message), 500);
    },
};

window.DailyAlerts = DailyAlerts;

function bootDailyAlerts() {
    if (window.__dailyAlertsBooted) return;
    window.__dailyAlertsBooted = true;
    DailyAlerts.init();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDailyAlerts);
} else {
    bootDailyAlerts();
}
