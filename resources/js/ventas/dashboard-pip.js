const PIP_STORAGE_KEY = 'ventas_pip_seller_ids';

const PIP_EXTRA_CSS = `
body.pip-mode {
    margin: 0;
    background: #111827;
    color: #fff;
    font-family: ui-sans-serif, system-ui, sans-serif;
    overflow-x: hidden;
}
.pip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: rgba(17, 24, 39, 0.95);
    border-bottom: 1px solid #374151;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
    color: #9ca3af;
}
.pip-header-count {
    color: #fff;
    font-size: 1rem;
    font-weight: 900;
}
#pip-sellers-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    padding: 0.5rem;
}
.pip-mode .seller-card .p-6 {
    padding: 0.75rem !important;
}
.pip-mode .seller-card h3 {
    font-size: 0.875rem !important;
}
.pip-mode .seller-card .w-16 {
    width: 2.5rem !important;
    height: 2.5rem !important;
}
.pip-mode .seller-card .text-xl {
    font-size: 0.75rem !important;
}
.pip-mode .seller-card .seller-timer,
.pip-mode .seller-card .break-timer {
    font-size: 1rem !important;
}
.pip-mode .seller-actions {
    margin-top: 0.5rem !important;
    padding-top: 0.5rem !important;
}
.pip-mode .seller-actions button,
.pip-mode .seller-actions .request-extension-btn {
    font-size: 0.75rem !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
#pip-rating-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(8px);
}
#pip-rating-modal.is-visible {
    display: flex;
}
.pip-rating-panel {
    width: 100%;
    max-width: 22rem;
    background: #111827;
    border: 2px solid #a855f7;
    border-radius: 1rem;
    padding: 1rem;
}
.pip-rating-stars {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin: 0.75rem 0;
}
.pip-rating-star {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.75rem;
    line-height: 1;
    color: #374151;
}
.pip-rating-star.is-active {
    color: #facc15;
}
.pip-rating-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
}
.pip-rating-tag {
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    border: 1px solid #374151;
    background: #1f2937;
    color: #9ca3af;
    cursor: pointer;
}
.pip-rating-tag.is-selected {
    background: #9333ea;
    border-color: #a855f7;
    color: #fff;
}
.pip-rating-textarea {
    width: 100%;
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.75rem;
    color: #fff;
    padding: 0.5rem;
    font-size: 0.75rem;
    margin-bottom: 0.75rem;
    min-height: 4rem;
    resize: vertical;
}
.pip-rating-actions {
    display: flex;
    gap: 0.5rem;
}
.pip-rating-actions button {
    flex: 1;
    border: none;
    border-radius: 0.75rem;
    padding: 0.6rem;
    font-weight: 800;
    font-size: 0.7rem;
    text-transform: uppercase;
    cursor: pointer;
}
.pip-rating-skip {
    background: #374151;
    color: #9ca3af;
}
.pip-rating-submit {
    background: #9333ea;
    color: #fff;
}
.pip-rating-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
#pip-mega-alert {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 0.75rem;
    backdrop-filter: blur(8px);
}
#pip-mega-alert.is-visible {
    display: flex;
}
#pip-mega-alert.pip-alert-premium {
    background: rgba(113, 63, 18, 0.95);
}
#pip-mega-alert.pip-alert-standard {
    background: rgba(30, 58, 138, 0.95);
}
.pip-alert-inner {
    text-align: center;
    width: 100%;
}
.pip-alert-title {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.pip-alert-box {
    border-radius: 1rem;
    padding: 1rem;
    border-width: 3px;
    border-style: solid;
}
.pip-alert-premium .pip-alert-box {
    border-color: #facc15;
    background: linear-gradient(to bottom right, #fefce8, #fff);
}
.pip-alert-standard .pip-alert-box {
    border-color: rgba(96, 165, 250, 0.5);
    background: #fff;
}
.pip-alert-label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #9ca3af;
    font-weight: 700;
    margin-bottom: 0.15rem;
}
.pip-alert-seller {
    font-size: 1.25rem;
    font-weight: 900;
    line-height: 1.2;
    margin-bottom: 0.75rem;
}
.pip-alert-premium .pip-alert-seller {
    color: #ca8a04;
}
.pip-alert-standard .pip-alert-seller {
    color: #2563eb;
}
.pip-alert-client {
    font-size: 1rem;
    font-weight: 900;
    color: #1f2937;
    line-height: 1.2;
}
.pip-alert-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.25rem;
    margin-bottom: 0.25rem;
}
.pip-alert-badge {
    font-size: 0.5rem;
    font-weight: 900;
    text-transform: uppercase;
    padding: 0.15rem 0.4rem;
    border-radius: 9999px;
    color: #fff;
}
.pip-alert-badge-premium {
    background: #eab308;
}
.pip-alert-badge-priority {
    background: #3b82f6;
}
.pip-alert-btn {
    margin-top: 0.75rem;
    width: 100%;
    padding: 0.6rem;
    border-radius: 0.75rem;
    font-weight: 900;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #fff;
    border: none;
    cursor: pointer;
}
.pip-alert-premium .pip-alert-btn {
    color: #713f12;
}
.pip-alert-standard .pip-alert-btn {
    color: #1e3a8a;
}
`;

function copyStylesToPip(pipWindow) {
    [...document.styleSheets].forEach((styleSheet) => {
        try {
            const cssRules = [...styleSheet.cssRules].map((rule) => rule.cssText).join('');
            const style = pipWindow.document.createElement('style');
            style.textContent = cssRules;
            pipWindow.document.head.appendChild(style);
        } catch {
            if (styleSheet.href) {
                const link = pipWindow.document.createElement('link');
                link.rel = 'stylesheet';
                link.href = styleSheet.href;
                pipWindow.document.head.appendChild(link);
            }
        }
    });

    const extra = pipWindow.document.createElement('style');
    extra.textContent = PIP_EXTRA_CSS;
    pipWindow.document.head.appendChild(extra);
}

function buildPipShell(pipWindow) {
    const doc = pipWindow.document;
    doc.body.className = 'pip-mode';
    doc.body.innerHTML = `
        <div class="pip-header">
            <span id="pip-header-label">Monitoreo</span>
            <span>En fila: <span class="pip-header-count" id="pip-waiting-count">0</span></span>
        </div>
        <div id="pip-sellers-grid"></div>
        <div id="pip-mega-alert" class="pip-alert-standard">
            <div class="pip-alert-inner">
                <p class="pip-alert-title" id="pip-alert-title">Nueva Asignación</p>
                <div class="pip-alert-box">
                    <p class="pip-alert-label">Vendedor</p>
                    <p class="pip-alert-seller" id="pip-alert-seller"></p>
                    <p class="pip-alert-label" id="pip-alert-folio-label">Cliente</p>
                    <div class="pip-alert-badges" id="pip-alert-badges"></div>
                    <p class="pip-alert-client" id="pip-alert-client"></p>
                </div>
                <button type="button" class="pip-alert-btn" id="pip-alert-dismiss">Enterado</button>
            </div>
        </div>
        <div id="pip-rating-modal">
            <div class="pip-rating-panel">
                <h3 style="text-align:center;font-weight:900;font-size:1rem;margin:0 0 0.25rem;">Califica tu Venta</h3>
                <p style="text-align:center;color:#9ca3af;font-size:0.7rem;margin:0 0 0.5rem;">¿Cómo fue tu experiencia con el cliente?</p>
                <div class="pip-rating-stars" id="pip-rating-stars"></div>
                <div class="pip-rating-tags" id="pip-rating-tags"></div>
                <textarea class="pip-rating-textarea" id="pip-rating-comment" placeholder="Comentarios adicionales (Opcional)..."></textarea>
                <div class="pip-rating-actions">
                    <button type="button" class="pip-rating-skip" id="pip-rating-skip">Omitir</button>
                    <button type="button" class="pip-rating-submit" id="pip-rating-submit" disabled>Enviar</button>
                </div>
            </div>
        </div>
    `;

    doc.getElementById('pip-alert-dismiss').addEventListener('click', () => {
        hidePipMegaAlert(pipWindow);
        if (typeof pipWindow.__pipAlertDismissCallback === 'function') {
            pipWindow.__pipAlertDismissCallback();
            pipWindow.__pipAlertDismissCallback = null;
        }
    });
}

const POPUP_WINDOW_NAME = 'ventas-dashboard-extract';

export function resolvePipSupport() {
    const isSecure = typeof window !== 'undefined' && window.isSecureContext === true;
    const hasApi = typeof window !== 'undefined' && 'documentPictureInPicture' in window;

    if (hasApi) {
        return {
            canUseDocumentPip: true,
            canUsePopupFallback: true,
            isSecureContext: isSecure,
            mode: 'document-pip',
            hint: 'Ventana always-on-top visible sobre Excel, WhatsApp y otras apps.',
            buttonLabel: 'Abrir ventana flotante',
        };
    }

    if (!isSecure) {
        return {
            canUseDocumentPip: false,
            canUsePopupFallback: true,
            isSecureContext: false,
            mode: 'popup',
            hint: 'PiP always-on-top requiere HTTPS. En HTTP se abrirá una ventana separada (no queda encima de otras apps). Para PiP real, use https:// en la URL.',
            buttonLabel: 'Abrir ventana separada',
        };
    }

    return {
        canUseDocumentPip: false,
        canUsePopupFallback: true,
        isSecureContext: isSecure,
        mode: 'popup',
        hint: 'PiP no está disponible en este navegador. Se abrirá una ventana separada.',
        buttonLabel: 'Abrir ventana separada',
    };
}

export function isPipSupported() {
    return resolvePipSupport().canUseDocumentPip;
}

export function loadPipSelection(pipSellers, linkedEmployeeId) {
    try {
        const stored = localStorage.getItem(PIP_STORAGE_KEY);
        if (stored) {
            const ids = JSON.parse(stored);
            if (Array.isArray(ids) && ids.length > 0) {
                return ids.map(Number);
            }
        }
    } catch {
        // ignore invalid storage
    }

    if (linkedEmployeeId) {
        return [Number(linkedEmployeeId)];
    }

    return (pipSellers || []).map((s) => Number(s.id));
}

export function persistPipSelection(selectedIds) {
    localStorage.setItem(PIP_STORAGE_KEY, JSON.stringify(selectedIds.map(Number)));
}

export function filterGridHtml(html, selectedSellerIds) {
    const selected = new Set(selectedSellerIds.map(Number));
    const temp = document.createElement('div');
    temp.innerHTML = html;
    temp.querySelectorAll('.seller-card').forEach((card) => {
        const employeeId = Number(card.dataset.employeeId);
        if (!selected.has(employeeId)) {
            card.remove();
        }
    });
    return temp.innerHTML;
}

export function updatePipHeaderLabel(pipWindow, selectedSellerIds, pipSellers) {
    const labelEl = pipWindow.document.getElementById('pip-header-label');
    if (!labelEl) return;

    const names = (pipSellers || [])
        .filter((s) => selectedSellerIds.includes(Number(s.id)))
        .map((s) => s.name.split(/\s+/)[0]);

    if (names.length === 0) {
        labelEl.textContent = 'Sin vendedores';
    } else if (names.length === 1) {
        labelEl.textContent = names[0];
    } else if (names.length <= 3) {
        labelEl.textContent = names.join(', ');
    } else {
        labelEl.textContent = `${names.length} vendedores`;
    }
}

export function syncPipWaitingCount(pipWindow, count) {
    const el = pipWindow?.document?.getElementById('pip-waiting-count');
    if (el) {
        el.textContent = String(count);
    }
}

export function syncPipGrid(pipWindow, html, selectedSellerIds, dashboard) {
    if (!pipWindow || pipWindow.closed) {
        return;
    }

    const grid = pipWindow.document.getElementById('pip-sellers-grid');
    if (!grid) {
        return;
    }

    const filtered = filterGridHtml(html, selectedSellerIds);
    grid.innerHTML = filtered;

    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
        window.Alpine.initTree(grid);
    }

    if (dashboard) {
        dashboard.applyServeTimerAnchorsInDocument?.(pipWindow.document);
        dashboard.applyExtensionOverridesToGridInDocument?.(pipWindow.document);
        updateVisualTimersInDocument(pipWindow.document, dashboard);
    }
}

export function showPipMegaAlert(pipWindow, data, onDismiss) {
    if (!pipWindow || pipWindow.closed) {
        return;
    }

    const overlay = pipWindow.document.getElementById('pip-mega-alert');
    if (!overlay) {
        return;
    }

    const isPremium = !!data.use_premium_alert;
    overlay.classList.toggle('pip-alert-premium', isPremium);
    overlay.classList.toggle('pip-alert-standard', !isPremium);
    overlay.classList.add('is-visible');

    const titleEl = pipWindow.document.getElementById('pip-alert-title');
    const sellerEl = pipWindow.document.getElementById('pip-alert-seller');
    const folioLabelEl = pipWindow.document.getElementById('pip-alert-folio-label');
    const clientEl = pipWindow.document.getElementById('pip-alert-client');
    const badgesEl = pipWindow.document.getElementById('pip-alert-badges');

    if (titleEl) {
        titleEl.textContent = isPremium ? 'Cliente Premium' : 'Nueva Asignación';
        titleEl.style.color = isPremium ? '#fef08a' : '#bfdbfe';
    }
    if (sellerEl) sellerEl.textContent = data.seller || '';
    if (folioLabelEl) folioLabelEl.textContent = `Cliente (Turno: ${data.folio || ''})`;
    if (clientEl) clientEl.textContent = data.client || '';

    if (badgesEl) {
        badgesEl.innerHTML = '';
        if (isPremium) {
            const badge = pipWindow.document.createElement('span');
            badge.className = 'pip-alert-badge pip-alert-badge-premium';
            badge.textContent = data.client_type_label || 'Premium';
            badgesEl.appendChild(badge);
        }
        if (data.has_disability) {
            const badge = pipWindow.document.createElement('span');
            badge.className = 'pip-alert-badge pip-alert-badge-priority';
            badge.textContent = 'Prioridad';
            badgesEl.appendChild(badge);
        }
    }

    pipWindow.__pipAlertDismissCallback = onDismiss;
}

export function hidePipMegaAlert(pipWindow) {
    if (!pipWindow || pipWindow.closed) {
        return;
    }
    const overlay = pipWindow.document.getElementById('pip-mega-alert');
    if (overlay) {
        overlay.classList.remove('is-visible', 'pip-alert-premium', 'pip-alert-standard');
    }
}

export function updateVisualTimersInDocument(doc, dashboard) {
    if (!doc || !dashboard) {
        return;
    }

    const now = Date.now();

    doc.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
        const queueId = card.dataset.queueId;
        let startTime = queueId && dashboard.serveTimerAnchors[queueId]
            ? dashboard.serveTimerAnchors[queueId]
            : parseInt(card.dataset.startTime, 10);

        if (queueId && Number.isFinite(startTime) && startTime > 0) {
            const existing = dashboard.serveTimerAnchors[queueId];
            if (!existing || startTime < existing) {
                dashboard.serveTimerAnchors[queueId] = startTime;
            } else {
                startTime = existing;
            }
            card.dataset.startTime = String(startTime);
        }

        if (!Number.isFinite(startTime) || startTime <= 0) {
            return;
        }

        let elapsedSecs = Math.floor((now - startTime) / 1000);
        if (elapsedSecs < 0) elapsedSecs = 0;

        const mins = Math.floor(elapsedSecs / 60);
        const secs = elapsedSecs % 60;
        const timerEl = card.querySelector('.seller-timer');
        if (timerEl) {
            timerEl.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            timerEl.className = mins >= dashboard.timingSettings.attentionMins
                ? 'seller-timer text-xl font-mono font-bold text-yellow-500 tracking-wider'
                : 'seller-timer text-xl font-mono font-bold text-gray-300 tracking-wider';
        }

        const extensionCount = parseInt(card.dataset.extensionCount, 10) || 0;
        const lastExtendedAt = parseInt(card.dataset.lastExtendedAt, 10) || 0;
        const warningEl = card.querySelector('.extension-warning');
        const phase = dashboard.resolveProrrogaPhase(startTime, extensionCount, lastExtendedAt, now);
        const { inRequestWindow, inExtensionGrace } = phase;

        if (inRequestWindow) {
            if (warningEl) {
                warningEl.innerText = 'Tiempo expirado. Prórroga requerida';
                warningEl.classList.remove('hidden');
            }
            const prorrogaBtn = card.querySelector('.request-extension-btn');
            const prorrogaLabel = card.querySelector('.request-extension-label');
            if (prorrogaBtn) prorrogaBtn.classList.remove('hidden');
            if (prorrogaLabel) prorrogaLabel.classList.add('hidden');
        } else if (inExtensionGrace) {
            if (warningEl) {
                const remainingSecs = Math.max(0, Math.ceil((phase.grantedDeadlineMs - now) / 1000));
                const rMins = Math.floor(remainingSecs / 60);
                const rSecs = remainingSecs % 60;
                warningEl.innerText = `Prórroga activa: ${rMins.toString().padStart(2, '0')}:${rSecs.toString().padStart(2, '0')} restantes`;
                warningEl.classList.remove('hidden');
            }
            const prorrogaBtn = card.querySelector('.request-extension-btn');
            const prorrogaLabel = card.querySelector('.request-extension-label');
            if (prorrogaBtn) prorrogaBtn.classList.add('hidden');
            if (prorrogaLabel) {
                prorrogaLabel.classList.remove('hidden');
                prorrogaLabel.innerText = `Prórroga solicitada (${extensionCount})`;
            }
        } else {
            if (warningEl) warningEl.classList.add('hidden');
            const prorrogaBtn = card.querySelector('.request-extension-btn');
            const prorrogaLabel = card.querySelector('.request-extension-label');
            if (prorrogaBtn) prorrogaBtn.classList.add('hidden');
            if (prorrogaLabel) prorrogaLabel.classList.add('hidden');
        }
    });

    doc.querySelectorAll('.seller-card[data-on-break="true"]').forEach((card) => {
        const breakStartTime = parseInt(card.dataset.breakStartTime, 10);
        const breakReason = card.dataset.breakReason;
        const lunchLeft = parseInt(card.dataset.lunchLeft, 10) || 1800;
        if (!breakStartTime) {
            return;
        }

        const timerEl = card.querySelector('.break-timer');
        if (!timerEl) {
            return;
        }

        const elapsedSecs = Math.floor((now - breakStartTime) / 1000);

        if (breakReason === 'LUNCH') {
            const remaining = lunchLeft - elapsedSecs;
            if (remaining < 0) {
                const excess = Math.abs(remaining);
                const eMins = Math.floor(excess / 60);
                const eSecs = excess % 60;
                timerEl.innerText = `-${eMins.toString().padStart(2, '0')}:${eSecs.toString().padStart(2, '0')}`;
                timerEl.className = 'break-timer text-2xl font-mono font-black text-red-500 tracking-wider animate-pulse';
            } else {
                const rMins = Math.floor(remaining / 60);
                const rSecs = remaining % 60;
                timerEl.innerText = `${rMins.toString().padStart(2, '0')}:${rSecs.toString().padStart(2, '0')}`;
                timerEl.className = 'break-timer text-2xl font-mono font-bold text-yellow-400 tracking-wider';
            }
        } else {
            const absSecs = Math.abs(elapsedSecs);
            const mins = Math.floor(absSecs / 60);
            const secs = absSecs % 60;
            const timeStr = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            if (elapsedSecs < 0) {
                timerEl.innerText = `-${timeStr}`;
                timerEl.className = 'break-timer text-2xl font-mono font-black text-green-400 tracking-wider animate-pulse';
            } else {
                timerEl.innerText = timeStr;
                if (mins >= 30) {
                    timerEl.className = 'break-timer text-2xl font-mono font-black text-red-500 tracking-wider animate-pulse';
                } else if (mins >= 25) {
                    timerEl.className = 'break-timer text-2xl font-mono font-bold text-yellow-500 tracking-wider';
                } else {
                    timerEl.className = 'break-timer text-2xl font-mono font-bold text-yellow-300 tracking-wider';
                }
            }
        }
    });

    doc.querySelectorAll('.seller-card[data-online="true"]').forEach((card) => {
        const lastActionAt = parseInt(card.dataset.lastActionAt, 10);
        if (!lastActionAt) {
            return;
        }

        const elapsedSecs = Math.floor((now - lastActionAt) / 1000);
        const delayContainer = card.querySelector('.delay-container');
        const onlineDots = card.querySelector('.online-dots');
        const delayTimerEl = card.querySelector('.delay-timer');

        if (elapsedSecs < 10) {
            if (delayContainer) delayContainer.style.display = 'block';
            if (onlineDots) onlineDots.style.display = 'none';
            if (delayTimerEl) delayTimerEl.innerText = `${10 - elapsedSecs}s`;
        } else {
            if (delayContainer) delayContainer.style.display = 'none';
            if (onlineDots) onlineDots.style.display = 'block';
        }
    });
}

function renderExtractRatingStars(childWindow, dashboard) {
    const doc = childWindow.document;
    const starsEl = doc.getElementById('pip-rating-stars');
    const tagsEl = doc.getElementById('pip-rating-tags');
    const submitBtn = doc.getElementById('pip-rating-submit');
    if (!starsEl || !tagsEl || !submitBtn) return;

    starsEl.innerHTML = '';
    for (let star = 1; star <= 5; star += 1) {
        const btn = doc.createElement('button');
        btn.type = 'button';
        btn.className = `pip-rating-star${dashboard.ratingStars >= star ? ' is-active' : ''}`;
        btn.textContent = '★';
        btn.addEventListener('click', () => {
            dashboard.ratingStars = star;
            renderExtractRatingStars(childWindow, dashboard);
        });
        starsEl.appendChild(btn);
    }

    tagsEl.innerHTML = '';
    const tags = typeof dashboard.availableTags === 'function' ? dashboard.availableTags() : [];
    tags.forEach((tag) => {
        const btn = doc.createElement('button');
        btn.type = 'button';
        btn.className = `pip-rating-tag${dashboard.ratingTags.includes(tag) ? ' is-selected' : ''}`;
        btn.textContent = tag;
        btn.addEventListener('click', () => {
            dashboard.toggleTag(tag);
            renderExtractRatingStars(childWindow, dashboard);
        });
        tagsEl.appendChild(btn);
    });

    submitBtn.disabled = dashboard.ratingStars === 0;
}

export function showExtractRatingModal(childWindow, dashboard) {
    if (!childWindow || childWindow.closed) return;

    const overlay = childWindow.document.getElementById('pip-rating-modal');
    const commentEl = childWindow.document.getElementById('pip-rating-comment');
    const skipBtn = childWindow.document.getElementById('pip-rating-skip');
    const submitBtn = childWindow.document.getElementById('pip-rating-submit');
    if (!overlay || !commentEl || !skipBtn || !submitBtn) return;

    commentEl.value = dashboard.ratingComment || '';
    renderExtractRatingStars(childWindow, dashboard);
    overlay.classList.add('is-visible');

    if (!childWindow.__ventasRatingWired) {
        childWindow.__ventasRatingWired = true;

        commentEl.addEventListener('input', () => {
            dashboard.ratingComment = commentEl.value;
        });

        skipBtn.addEventListener('click', () => {
            dashboard.ratingComment = commentEl.value;
            dashboard.skipRating();
        });

        submitBtn.addEventListener('click', () => {
            if (dashboard.ratingStars === 0) return;
            dashboard.ratingComment = commentEl.value;
            dashboard.submitRating();
        });
    }
}

export function hideExtractRatingModal(childWindow) {
    if (!childWindow || childWindow.closed) return;
    const overlay = childWindow.document.getElementById('pip-rating-modal');
    if (overlay) overlay.classList.remove('is-visible');
}

function wireExtractWindowInteractions(childWindow, dashboard) {
    if (!childWindow || childWindow.closed || childWindow.__ventasExtractWired) {
        return;
    }

    childWindow.__ventasExtractWired = true;
    const doc = childWindow.document;

    doc.addEventListener('open-break-modal', (event) => {
        const detail = event.detail;
        if (!detail) return;
        dashboard.breakShiftId = detail.id;
        dashboard.lunchSecondsLeft = detail.lunchLeft;
        dashboard.showBreakModal = true;
    });

    doc.getElementById('pip-sellers-grid')?.addEventListener('click', (event) => {
        const extensionBtn = event.target.closest('.request-extension-btn');
        if (extensionBtn && !extensionBtn.classList.contains('hidden')) {
            const card = extensionBtn.closest('.seller-card');
            if (!card?.dataset.queueId) return;
            event.preventDefault();
            dashboard.requestExtension({
                queue_id: Number(card.dataset.queueId),
                seller_name: card.dataset.sellerName || 'Vendedor',
            });
            return;
        }

        const finishBtn = event.target.closest('[data-action="finish-service"]');
        if (finishBtn) {
            event.preventDefault();
            const card = finishBtn.closest('.seller-card');
            if (!card?.dataset.shiftId || !card?.dataset.queueId) return;
            dashboard.processFinishService(Number(card.dataset.shiftId), Number(card.dataset.queueId));
            return;
        }

        const ratingBtn = event.target.closest('[data-action="open-rating"]');
        if (ratingBtn) {
            event.preventDefault();
            const card = ratingBtn.closest('.seller-card');
            if (!card?.dataset.shiftId) return;
            dashboard.openRatingModal(
                Number(card.dataset.shiftId),
                Number(ratingBtn.dataset.queueId || card.dataset.queueId || 0)
            );
        }
    });
}

function attachExtractWindow(dashboard, childWindow, mode) {
    copyStylesToPip(childWindow);
    buildPipShell(childWindow);

    dashboard.pipWindow = childWindow;
    dashboard.pipActive = true;
    dashboard.pipMode = mode;

    updatePipHeaderLabel(childWindow, dashboard.selectedPipSellerIds, dashboard.pipSellers);
    syncPipWaitingCount(childWindow, dashboard.waitingCount);

    if (mode === 'popup') {
        const labelEl = childWindow.document.getElementById('pip-header-label');
        if (labelEl) {
            labelEl.textContent = `${labelEl.textContent} · HTTP`;
        }
    }

    const mainGrid = document.getElementById('sellers-grid');
    if (mainGrid) {
        syncPipGrid(childWindow, mainGrid.innerHTML, dashboard.selectedPipSellerIds, dashboard);
    }

    wireExtractWindowInteractions(childWindow, dashboard);

    const onClose = () => {
        dashboard.pipActive = false;
        dashboard.pipWindow = null;
        dashboard.pipMode = null;
        hidePipMegaAlert(childWindow);
        hideExtractRatingModal(childWindow);
    };

    if (mode === 'document-pip') {
        childWindow.addEventListener('pagehide', onClose);
    } else {
        const closeWatcher = window.setInterval(() => {
            if (childWindow.closed) {
                window.clearInterval(closeWatcher);
                onClose();
            }
        }, 500);
    }

    return childWindow;
}

async function openDocumentPipWindow(dashboard) {
    const pipWindow = await window.documentPictureInPicture.requestWindow({
        width: 400,
        height: 600,
    });

    return attachExtractWindow(dashboard, pipWindow, 'document-pip');
}

function openPopupWindow(dashboard) {
    const popup = window.open(
        'about:blank',
        POPUP_WINDOW_NAME,
        'width=420,height=640,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes'
    );

    if (!popup) {
        alert('El navegador bloqueó la ventana emergente. Permita ventanas emergentes para este sitio e intente de nuevo.');
        return null;
    }

    return attachExtractWindow(dashboard, popup, 'popup');
}

export async function openExtractWindow(dashboard) {
    if (dashboard.selectedPipSellerIds.length === 0) {
        alert('Selecciona al menos un vendedor para extraer el dashboard.');
        return null;
    }

    if (dashboard.pipWindow && !dashboard.pipWindow.closed) {
        dashboard.pipWindow.focus();
        return dashboard.pipWindow;
    }

    persistPipSelection(dashboard.selectedPipSellerIds);

    const support = resolvePipSupport();

    try {
        if (support.canUseDocumentPip) {
            return await openDocumentPipWindow(dashboard);
        }
    } catch (error) {
        console.warn('Document PiP falló, usando ventana separada:', error);
    }

    if (!support.canUsePopupFallback) {
        alert('No se pudo abrir la ventana flotante.');
        return null;
    }

    return openPopupWindow(dashboard);
}

/** @deprecated Use openExtractWindow */
export async function openPipWindow(dashboard) {
    return openExtractWindow(dashboard);
}

export function isAlertForSelectedSeller(alert, selectedSellerIds) {
    if (!alert?.seller_id) {
        return true;
    }
    return selectedSellerIds.map(Number).includes(Number(alert.seller_id));
}

window.VentasPip = {
    resolvePipSupport,
    isPipSupported,
    openExtractWindow,
    loadPipSelection,
    persistPipSelection,
    filterGridHtml,
    updatePipHeaderLabel,
    syncPipWaitingCount,
    syncPipGrid,
    showPipMegaAlert,
    hidePipMegaAlert,
    updateVisualTimersInDocument,
    showExtractRatingModal,
    hideExtractRatingModal,
    openPipWindow: openExtractWindow,
    isAlertForSelectedSeller,
};
