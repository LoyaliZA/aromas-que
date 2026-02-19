<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla de Turnos - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .ticket-enter {
            animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes pulseHighlight {
            0% { box-shadow: 0 0 0 0 rgba(253, 201, 116, 0.5); }
            70% { box-shadow: 0 0 0 20px rgba(253, 201, 116, 0); }
            100% { box-shadow: 0 0 0 0 rgba(253, 201, 116, 0); }
        }
        .pulse-ticket {
            animation: pulseHighlight 2s infinite;
        }
        .fade-transition {
            transition: opacity 1s ease-in-out;
        }
    </style>
</head>
<body class="bg-aromas-main text-white h-screen w-screen overflow-hidden flex font-sans relative">

    <audio id="chimeSound" src="/audio/timbre.mp3" preload="auto"></audio>

    <div id="alert-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 opacity-0 pointer-events-none transition-opacity duration-300 backdrop-blur-sm">
        <div class="bg-aromas-secondary border-4 border-aromas-highlight rounded-3xl p-16 shadow-[0_0_100px_rgba(253,201,116,0.4)] flex flex-col items-center justify-center text-center transform scale-90 transition-transform duration-300" id="alert-modal-content">
            <h2 class="text-4xl font-bold text-aromas-highlight uppercase tracking-widest mb-4">NUEVO TURNO</h2>
            <div id="modal-turn-number" class="text-[10rem] font-black text-white leading-none mb-6">--</div>
            <div id="modal-client-name" class="text-5xl font-bold text-gray-300 mb-10">--</div>
            
            <div class="bg-aromas-main px-12 py-6 rounded-2xl border-2 border-aromas-tertiary/30 w-full">
                <span id="modal-dest-label" class="block text-2xl uppercase text-aromas-tertiary font-bold tracking-widest mb-2">Pase a:</span>
                <span id="modal-dest-name" class="block text-6xl font-black text-aromas-highlight uppercase">--</span>
            </div>
        </div>
    </div>

    <div class="w-2/3 h-full bg-black relative flex flex-col justify-center items-center overflow-hidden border-r border-aromas-tertiary/30">
        <div class="absolute inset-0 flex items-center justify-center opacity-10 z-0">
            <img src="/images/logo_blanco.png" alt="Aromas Fondo" class="w-1/2">
        </div>

        <div id="media-container" class="z-10 w-full h-full flex items-center justify-center relative bg-black">
            </div>
    </div>

    <div class="w-1/3 h-full bg-aromas-secondary flex flex-col shadow-2xl relative z-20">
        <div class="bg-aromas-highlight text-aromas-main py-4 px-4 shadow-md text-center flex flex-col">
            <h2 class="text-3xl font-black uppercase tracking-widest leading-none">Turnos</h2>
            <span class="text-xs font-bold mt-1 opacity-80">ATENCIÓN A CLIENTES</span>
        </div>

        <div class="h-3/4 p-4 flex flex-col">
            <h3 class="text-lg font-bold text-aromas-tertiary uppercase tracking-wider mb-2 border-b border-aromas-tertiary/30 pb-2">Atendiendo</h3>
            <div id="serving-list" class="space-y-3 flex-1 overflow-hidden">
                <div class="text-center text-aromas-tertiary mt-10">Cargando turnos...</div>
            </div>
        </div>

        <div class="h-1/4 bg-aromas-main p-4 border-t border-aromas-tertiary/30 flex flex-col">
            <div class="flex justify-between items-end mb-3">
                <h3 class="text-sm font-bold text-aromas-tertiary uppercase tracking-wider">En Espera</h3>
                <div class="text-xs text-aromas-highlight font-bold bg-aromas-highlight/10 px-2 py-1 rounded border border-aromas-highlight/20">
                    Tiempo Aprox: <span id="wait-time">0</span> min
                </div>
            </div>
            <div id="waiting-list" class="space-y-2 overflow-y-auto h-full pb-2 pr-2">
            </div>
        </div>
    </div>

    <script>
        let tvAds = @json(isset($ads) ? $ads->map(function($ad) {
            return [
                'type' => $ad->media_type,
                'url' => $ad->media_url,
                'duration' => $ad->duration_seconds * 1000
            ];
        })->values() : []);
        
        let lastAdsData = JSON.stringify(tvAds); 

        document.addEventListener('DOMContentLoaded', function() {
            const servingList = document.getElementById('serving-list');
            const waitingList = document.getElementById('waiting-list');
            const waitTimeEl = document.getElementById('wait-time');
            const chimeSound = document.getElementById('chimeSound');
            
            let lastCalledId = null;
            let lastServingData = '';
            let lastWaitingData = '';
            
            const alertModal = document.getElementById('alert-modal');
            const alertModalContent = document.getElementById('alert-modal-content');
            let isAlertActive = false; 

            const mediaContainer = document.getElementById('media-container');
            let currentAdIndex = 0;
            let carrouselTimer = null;
            let currentVideoElement = null;

            let spanishVoice = null;
            window.speechSynthesis.onvoiceschanged = () => {
                const voices = window.speechSynthesis.getVoices();
                spanishVoice = voices.find(v => v.name.includes('Google') && v.lang.includes('es'))
                            || voices.find(v => v.name.includes('Natural') && v.lang.includes('es'))
                            || voices.find(v => v.lang.includes('es-MX')) 
                            || voices.find(v => v.lang.includes('es'));
            };

            // ==========================================
            // LÓGICA DEL CARRUSEL DE PUBLICIDAD
            // ==========================================
            function playAd(index) {
                clearTimeout(carrouselTimer);
                mediaContainer.innerHTML = ''; 
                currentVideoElement = null;

                if (tvAds.length === 0) {
                    mediaContainer.innerHTML = `
                        <div class="text-center space-y-4 z-20">
                            <img src="/images/logo_blanco.png" alt="Aromas Logo" class="w-64 mx-auto mb-8">
                            <h1 class="text-4xl font-bold text-gray-300 tracking-widest">BIENVENIDOS</h1>
                            <p class="text-xl text-aromas-tertiary">Tome su turno en la entrada</p>
                        </div>
                    `;
                    return;
                }

                const ad = tvAds[index];

                if (ad.type === 'IMAGE') {
                    const img = document.createElement('img');
                    img.src = ad.url;
                    img.className = 'w-full h-full object-contain fade-transition opacity-0';
                    mediaContainer.appendChild(img);
                    
                    setTimeout(() => { img.classList.remove('opacity-0'); }, 50);
                    carrouselTimer = setTimeout(nextAd, ad.duration);

                } else if (ad.type === 'VIDEO') {
                    const video = document.createElement('video');
                    video.src = ad.url;
                    video.className = 'w-full h-full object-contain fade-transition opacity-0';
                    video.muted = false; 
                    video.volume = 1.0;
                    video.playsInline = true;
                    
                    mediaContainer.appendChild(video);
                    currentVideoElement = video;

                    setTimeout(() => { video.classList.remove('opacity-0'); }, 50);

                    video.play().catch(e => {
                        console.error("Autoplay bloqueado. Haz clic en la pantalla.", e);
                        carrouselTimer = setTimeout(nextAd, 5000); 
                    });

                    video.onended = nextAd;
                }
            }

            function nextAd() {
                if (isAlertActive || tvAds.length === 0) return;
                currentAdIndex++;
                if (currentAdIndex >= tvAds.length) {
                    currentAdIndex = 0; 
                }
                playAd(currentAdIndex);
            }

            function pauseCarrousel() {
                isAlertActive = true;
                clearTimeout(carrouselTimer); 
                if (currentVideoElement) {
                    currentVideoElement.pause(); 
                }
            }

            function resumeCarrousel() {
                isAlertActive = false;
                
                if (tvAds.length === 0) {
                    playAd(0);
                    return;
                }

                if (currentAdIndex >= tvAds.length) {
                    currentAdIndex = 0;
                }

                const currentAd = tvAds[currentAdIndex];
                
                if (currentAd && currentAd.type === 'VIDEO' && currentVideoElement) {
                    currentVideoElement.play().catch(e => console.error("Error al reanudar video", e)); 
                } else if (currentAd && currentAd.type === 'IMAGE') {
                    carrouselTimer = setTimeout(nextAd, currentAd.duration);
                } else {
                    playAd(currentAdIndex);
                }
            }

            // ==========================================
            // LÓGICA DE TURNOS Y DATOS (AJAX)
            // ==========================================
            function fetchQueueData() {
                fetch('/tv', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    const currentServingStr = JSON.stringify(data.serving);
                    const currentWaitingStr = JSON.stringify(data.waiting);

                    if (currentServingStr !== lastServingData) {
                        renderServing(data.serving);
                        lastServingData = currentServingStr;
                    }

                    if (currentWaitingStr !== lastWaitingData) {
                        renderWaiting(data.waiting);
                        calculateWaitTime(data.waiting.length);
                        lastWaitingData = currentWaitingStr;
                    }

                    if (data.ads) {
                        const currentAdsStr = JSON.stringify(data.ads);
                        if (currentAdsStr !== lastAdsData) {
                            tvAds = data.ads;
                            lastAdsData = currentAdsStr;
                            
                            if (!isAlertActive) {
                                currentAdIndex = 0; 
                                playAd(currentAdIndex);
                            }
                        }
                    }
                })
                .catch(error => console.error('Error al actualizar la TV:', error));
            }

            function calculateWaitTime(peopleCount) {
                const estimatedMinutes = peopleCount * 3;
                waitTimeEl.innerText = estimatedMinutes > 0 ? estimatedMinutes : '< 1';
            }

            function renderServing(servingArray) {
                if (servingArray.length === 0) {
                    servingList.innerHTML = '<div class="text-center text-aromas-tertiary mt-10 italic">No hay clientes siendo atendidos.</div>';
                    return;
                }

                let html = '';
                let newestClient = servingArray[0]; 

                servingArray.forEach((ticket, index) => {
                    const isNewest = index === 0;
                    const destName = ticket.service_type === 'CASHIER' ? 'Caja Principal' : (ticket.assigned_shift ? ticket.assigned_shift.employee.full_name : 'Vendedor');
                    const ticketNumber = ticket.turn_number ? ticket.turn_number : 'S/N';
                    
                    if (isNewest) {
                        html += `
                            <div class="ticket-enter bg-aromas-secondary border-2 border-aromas-highlight rounded-xl p-4 flex flex-col justify-center items-center shadow-2xl pulse-ticket mb-4">
                                <span class="text-sm font-bold uppercase tracking-widest text-aromas-highlight mb-1">Turno Actual</span>
                                <div class="text-6xl font-black text-white tracking-tighter mb-2">${ticketNumber}</div>
                                <div class="text-lg font-bold text-gray-300 w-full text-center mb-4">${ticket.client_name}</div>
                                
                                <div class="w-full bg-aromas-main rounded-lg p-3 text-center border border-aromas-tertiary/30">
                                    <span class="block text-[10px] uppercase text-aromas-tertiary font-bold tracking-wider mb-1">${ticket.service_type === 'CASHIER' ? 'Pasar a:' : 'Vendedor asignado:'}</span>
                                    <span class="block text-xl font-black ${ticket.service_type === 'CASHIER' ? 'text-green-500' : 'text-aromas-highlight'} uppercase tracking-wider truncate">
                                        ${destName}
                                    </span>
                                </div>
                            </div>
                            <h4 class="text-xs font-bold text-aromas-tertiary uppercase tracking-widest mb-2 mt-2">Turnos Anteriores</h4>
                        `;
                    } else {
                        if (index <= 5) { 
                            const destColor = ticket.service_type === 'CASHIER' ? 'text-green-500' : 'text-aromas-highlight';
                            const labelText = ticket.service_type === 'CASHIER' ? 'Caja' : 'Vendedor';
                            html += `
                                <div class="bg-aromas-main border border-aromas-tertiary/20 rounded-lg p-3 flex justify-between items-center mb-2">
                                    <div>
                                        <div class="text-lg font-bold text-white">${ticketNumber}</div>
                                        <div class="text-[11px] text-gray-400 truncate w-24">${ticket.client_name}</div>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <span class="text-[9px] text-aromas-tertiary uppercase font-bold">${labelText}:</span>
                                        <div class="text-sm font-bold ${destColor} truncate w-28 uppercase">${destName}</div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                });

                servingList.innerHTML = html;

                if (newestClient && newestClient.id !== lastCalledId) {
                    if (lastCalledId !== null) {
                        triggerActiveInterruption(newestClient);
                    }
                    lastCalledId = newestClient.id; 
                }
            }

            function renderWaiting(waitingArray) {
                if (waitingArray.length === 0) {
                    waitingList.innerHTML = '<div class="text-center text-aromas-tertiary mt-2 text-sm">Fila vacía</div>';
                    return;
                }

                let html = '';
                waitingArray.forEach(ticket => {
                    const destName = ticket.service_type === 'CASHIER' ? 'Caja' : 'Ventas';
                    const badgeColor = ticket.service_type === 'CASHIER' ? 'bg-green-500/10 text-green-500 border-green-500/30' : 'bg-aromas-highlight/10 text-aromas-highlight border-aromas-highlight/30';
                    const ticketNumber = ticket.turn_number ? ticket.turn_number : '--';

                    html += `
                        <div class="bg-aromas-secondary rounded-lg p-2 px-3 flex justify-between items-center border border-aromas-tertiary/20">
                            <div class="flex items-center gap-3">
                                <span class="font-black text-white">${ticketNumber}</span>
                                <span class="text-xs text-gray-300 truncate w-24">${ticket.client_name}</span>
                            </div>
                            <div class="text-[9px] font-bold px-2 py-1 rounded border ${badgeColor} uppercase tracking-wider">
                                ${destName}
                            </div>
                        </div>
                    `;
                });

                waitingList.innerHTML = html;
            }

            function triggerActiveInterruption(ticket) {
                const destName = ticket.service_type === 'CASHIER' ? 'Caja Principal' : (ticket.assigned_shift ? ticket.assigned_shift.employee.full_name : 'Un vendedor');
                const ticketNumber = ticket.turn_number ? ticket.turn_number : '--';
                const destLabel = ticket.service_type === 'CASHIER' ? 'Pase a:' : 'Vendedor asignado:';
                const destColor = ticket.service_type === 'CASHIER' ? 'text-green-500' : 'text-aromas-highlight';

                pauseCarrousel();

                document.getElementById('modal-turn-number').innerText = ticketNumber;
                document.getElementById('modal-client-name').innerText = ticket.client_name;
                document.getElementById('modal-dest-label').innerText = destLabel;
                
                const destElement = document.getElementById('modal-dest-name');
                destElement.innerText = destName;
                destElement.className = `block text-6xl font-black uppercase ${destColor}`;

                alertModal.classList.remove('opacity-0', 'pointer-events-none');
                alertModalContent.classList.remove('scale-90');
                alertModalContent.classList.add('scale-100');

                chimeSound.currentTime = 0; 
                chimeSound.play().catch(e => console.log("Se requiere clic inicial para audio."));

                let cleanNumber = ticketNumber;
                if(ticketNumber.includes('-')) {
                    let parts = ticketNumber.split('-');
                    cleanNumber = parseInt(parts[1], 10); 
                }
                let script = `Turno número ${cleanNumber}. Cliente ${ticket.client_name}, favor de pasar ${ticket.service_type === 'CASHIER' ? 'a caja principal' : 'con el vendedor ' + destName}.`;
                
                const utterance = new SpeechSynthesisUtterance(script);
                if (spanishVoice) utterance.voice = spanishVoice;
                utterance.rate = 0.9; 

                // --- NUEVA LÓGICA: CIERRE DINÁMICO ---
                let isModalClosed = false;

                const closeAlert = () => {
                    if (isModalClosed) return;
                    isModalClosed = true;
                    
                    alertModal.classList.add('opacity-0', 'pointer-events-none');
                    alertModalContent.classList.remove('scale-100');
                    alertModalContent.classList.add('scale-90');
                    
                    setTimeout(resumeCarrousel, 500); 
                };

                // El modal espera a que la voz termine de hablar, da 1 segundo extra y luego se cierra
                utterance.onend = () => {
                    setTimeout(closeAlert, 1000);
                };

                // Por si la API de voz falla
                utterance.onerror = () => {
                    closeAlert();
                };

                setTimeout(() => {
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.speak(utterance);
                    } else {
                        // Si el navegador no soporta voz, lo cerramos a los 6 segundos fijos
                        setTimeout(closeAlert, 6000);
                    }
                }, 1500); 

                // Respaldo de seguridad absoluto: Si la voz se traba por cualquier motivo, 
                // forzamos el cierre a los 15 segundos para que la TV nunca se quede pasmada.
                setTimeout(closeAlert, 15000); 
            }

            fetchQueueData(); 
            setInterval(fetchQueueData, 3000); 

            playAd(currentAdIndex);
        });
    </script>
</body>
</html>