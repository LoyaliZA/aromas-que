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

    {{-- AUDIOS (Normal y VIP) --}}
    <audio id="chimeSound" src="/audio/timbre.mp3" preload="auto"></audio>
    <audio id="vipChimeSound" src="/audio/bell_vip.mp3" preload="auto"></audio>

    <div id="alert-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 opacity-0 pointer-events-none transition-opacity duration-300 backdrop-blur-sm">
        <div class="bg-aromas-secondary border-4 border-aromas-highlight rounded-3xl p-16 shadow-[0_0_100px_rgba(253,201,116,0.4)] flex flex-col items-center justify-center text-center transform scale-90 transition-transform duration-300" id="alert-modal-content">
            <h2 id="modal-title" class="text-4xl font-bold text-aromas-highlight uppercase tracking-widest mb-4">NUEVO TURNO</h2>
            <div id="modal-turn-number" class="text-[10rem] font-black text-white leading-none mb-6">--</div>
            <div id="modal-client-name" class="text-6xl font-bold text-gray-200 mb-10">--</div>
            
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

    {{-- 1. PREPARAMOS LOS DATOS EN PHP PURO --}}
    @php
        $tvAdsData = [];
        if (isset($ads)) {
            $tvAdsData = $ads->map(function($ad) {
                return [
                    'type' => $ad->media_type,
                    'url' => $ad->media_url,
                    'duration' => $ad->duration_seconds * 1000,
                    'volume' => ($ad->volume ?? 100) / 100
                ];
            })->values();
        }
    @endphp

    <script>
        // 2. RECIBIMOS LA VARIABLE LIMPIA EN JAVASCRIPT
        let tvAds = @json($tvAdsData);
        let lastAdsData = JSON.stringify(tvAds.map(a => a.url)); 

        document.addEventListener('DOMContentLoaded', function() {
            const servingList = document.getElementById('serving-list');
            const waitingList = document.getElementById('waiting-list');
            const waitTimeEl = document.getElementById('wait-time');
            const chimeSound = document.getElementById('chimeSound');
            const vipChimeSound = document.getElementById('vipChimeSound');
            
            let lastCalledId = null;
            let lastServingData = '';
            let lastWaitingData = '';
            
            const alertModal = document.getElementById('alert-modal');
            const alertModalContent = document.getElementById('alert-modal-content');
            let isAlertActive = false; 

            let announcementQueue = [];
            let isAnnouncing = false;

            const mediaContainer = document.getElementById('media-container');
            let currentAdIndex = 0;
            let carrouselTimer = null;
            let currentVideoElement = null;

            let spanishVoice = null;

            const loadVoices = () => {
                const voices = window.speechSynthesis.getVoices();
                spanishVoice = voices.find(v => v.name.includes('Google') && v.lang.includes('es')) ||
                               voices.find(v => v.name.includes('Natural') && v.lang.includes('es')) ||
                               voices.find(v => v.lang.includes('es-MX')) ||
                               voices.find(v => v.lang.includes('es'));
            };
            loadVoices();
            window.speechSynthesis.onvoiceschanged = loadVoices;

            function playAd(index) {
                clearTimeout(carrouselTimer);
                mediaContainer.innerHTML = ''; 
                currentVideoElement = null;

                if (tvAds.length === 0) {
                    mediaContainer.innerHTML = `
                        <div class="text-center space-y-4 z-20">
                            <img src="/images/aromas_logo_blanco.png" alt="Aromas Logo" class="w-64 mx-auto mb-8">
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
                    
                    video.volume = ad.volume !== undefined ? ad.volume : 1.0;
                    video.muted = false; 
                    video.playsInline = true;
                    
                    mediaContainer.appendChild(video);
                    currentVideoElement = video;

                    setTimeout(() => { video.classList.remove('opacity-0'); }, 50);

                    video.play().catch(e => {
                        console.error("Autoplay bloqueado. Haz clic en la pantalla.", e);
                        video.muted = true; 
                        video.play();
                        carrouselTimer = setTimeout(nextAd, 5000); 
                    });

                    video.onended = nextAd;
                }
            }

            function nextAd() {
                if (isAlertActive || tvAds.length === 0) return;
                currentAdIndex++;
                if (currentAdIndex >= tvAds.length) currentAdIndex = 0; 
                playAd(currentAdIndex);
            }

            function pauseCarrousel() {
                isAlertActive = true;
                clearTimeout(carrouselTimer); 
                if (currentVideoElement) currentVideoElement.pause(); 
            }

            function resumeCarrousel() {
                isAlertActive = false;
                if (tvAds.length === 0) {
                    playAd(0);
                    return;
                }
                if (currentAdIndex >= tvAds.length) currentAdIndex = 0;

                const currentAd = tvAds[currentAdIndex];
                if (currentAd && currentAd.type === 'VIDEO' && currentVideoElement) {
                    currentVideoElement.play().catch(e => console.error("Error al reanudar video", e)); 
                } else if (currentAd && currentAd.type === 'IMAGE') {
                    carrouselTimer = setTimeout(nextAd, currentAd.duration);
                } else {
                    playAd(currentAdIndex);
                }
            }

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
                        tvAds = data.ads; 

                        const activeVideo = document.querySelector('video'); 
                        if (activeVideo) {
                            const currentAdData = tvAds.find(a => activeVideo.src.includes(a.url));
                            if (currentAdData && currentAdData.volume !== undefined) {
                                if (activeVideo.volume !== currentAdData.volume) {
                                    activeVideo.volume = currentAdData.volume;
                                }
                            }
                        }

                        const currentPlaylistStr = JSON.stringify(tvAds.map(a => a.url));
                        
                        if (currentPlaylistStr !== lastAdsData) {
                            lastAdsData = currentPlaylistStr; 
                            
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

            function queueAnnouncement(ticket) {
                announcementQueue.push(ticket);
                processAnnouncementQueue();
            }

            function processAnnouncementQueue() {
                if (isAnnouncing || announcementQueue.length === 0) return;

                isAnnouncing = true;
                const ticketToAnnounce = announcementQueue.shift(); 
                
                triggerActiveInterruption(ticketToAnnounce, () => {
                    isAnnouncing = false;
                    if (announcementQueue.length > 0) {
                        setTimeout(processAnnouncementQueue, 500);
                    } else {
                        resumeCarrousel();
                    }
                });
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
                    
                    const isVIP = ticket.client_type === 'VIP';
                    const ticketNumberDisplay = isVIP ? 'AVISO' : (ticket.turn_number ? ticket.turn_number : 'S/N');
                    const textNumberSize = isVIP ? 'text-4xl mt-2 mb-4 text-yellow-500' : 'text-6xl mb-2 text-white';
                    
                    if (isNewest) {
                        html += `
                            <div class="ticket-enter bg-aromas-secondary border-2 ${isVIP ? 'border-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.3)]' : 'border-aromas-highlight'} rounded-xl p-4 flex flex-col justify-center items-center shadow-2xl pulse-ticket mb-4">
                                <span class="text-sm font-bold uppercase tracking-widest ${isVIP ? 'text-yellow-500' : 'text-aromas-highlight'} mb-1">${isVIP ? 'Atención' : 'Turno Actual'}</span>
                                <div class="font-black tracking-tighter ${textNumberSize}">${ticketNumberDisplay}</div>
                                <div class="text-lg font-bold text-gray-300 w-full text-center mb-4 truncate" title="${ticket.client_name}">${ticket.client_name}</div>
                                
                                <div class="w-full bg-aromas-main rounded-lg p-3 text-center border border-aromas-tertiary/30">
                                    <span class="block text-[10px] uppercase text-aromas-tertiary font-bold tracking-wider mb-1">${ticket.service_type === 'CASHIER' ? 'Pasar a:' : 'Vendedor asignado:'}</span>
                                    <span class="block text-xl font-black ${ticket.service_type === 'CASHIER' ? 'text-green-500' : (isVIP ? 'text-yellow-500' : 'text-aromas-highlight')} uppercase tracking-wider truncate">
                                        ${destName}
                                    </span>
                                </div>
                            </div>
                            <h4 class="text-xs font-bold text-aromas-tertiary uppercase tracking-widest mb-2 mt-2">Turnos Anteriores</h4>
                        `;
                    } else {
                        if (index <= 5) { 
                            const destColor = ticket.service_type === 'CASHIER' ? 'text-green-500' : (isVIP ? 'text-yellow-500' : 'text-aromas-highlight');
                            const labelText = ticket.service_type === 'CASHIER' ? 'Caja' : 'Vendedor';
                            html += `
                                <div class="bg-aromas-main border border-aromas-tertiary/20 rounded-lg p-3 flex justify-between items-center mb-2">
                                    <div class="overflow-hidden pr-2">
                                        <div class="text-lg font-bold ${isVIP ? 'text-yellow-500' : 'text-white'}">${ticketNumberDisplay}</div>
                                        <div class="text-[11px] text-gray-400 truncate w-24" title="${ticket.client_name}">${ticket.client_name}</div>
                                    </div>
                                    <div class="text-right flex flex-col items-end min-w-[80px]">
                                        <span class="text-[9px] text-aromas-tertiary uppercase font-bold">${labelText}:</span>
                                        <div class="text-sm font-bold ${destColor} truncate w-full max-w-[100px] uppercase text-right" title="${destName}">${destName}</div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                });

                servingList.innerHTML = html;

                if (newestClient && newestClient.id !== lastCalledId) {
                    if (lastCalledId !== null) {
                        queueAnnouncement(newestClient);
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
                        <div class="bg-aromas-secondary rounded-lg p-2 px-3 flex justify-between items-center border border-aromas-tertiary/20 mb-2">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <span class="font-black text-white">${ticketNumber}</span>
                                <span class="text-xs text-gray-300 truncate w-24" title="${ticket.client_name}">${ticket.client_name}</span>
                            </div>
                            <div class="text-[9px] font-bold px-2 py-1 rounded border ${badgeColor} uppercase tracking-wider ml-2">
                                ${destName}
                            </div>
                        </div>
                    `;
                });

                waitingList.innerHTML = html;
            }

            function triggerActiveInterruption(ticket, onCompleteCallback) {
                const destName = ticket.service_type === 'CASHIER' ? 'Caja Principal' : (ticket.assigned_shift ? ticket.assigned_shift.employee.full_name : 'Un vendedor');
                const destLabel = ticket.service_type === 'CASHIER' ? 'Pase a:' : 'Vendedor asignado:';
                const destColor = ticket.service_type === 'CASHIER' ? 'text-green-500' : 'text-aromas-highlight';
                
                const isVIP = ticket.client_type === 'VIP';

                pauseCarrousel();

                const titleEl = document.getElementById('modal-title');
                const numberEl = document.getElementById('modal-turn-number');
                const nameEl = document.getElementById('modal-client-name');
                const destElement = document.getElementById('modal-dest-name');
                
                if (isVIP) {
                    titleEl.innerText = "AVISO DIRECTO";
                    numberEl.style.display = 'none'; 
                    nameEl.style.fontSize = '4.5rem'; 
                    destElement.className = "block text-6xl font-black uppercase text-yellow-500"; 
                } else {
                    titleEl.innerText = "NUEVO TURNO";
                    numberEl.style.display = 'block';
                    numberEl.innerText = ticket.turn_number ? ticket.turn_number : '--';
                    nameEl.style.fontSize = '3rem';
                    destElement.className = `block text-6xl font-black uppercase ${destColor}`;
                }

                nameEl.innerText = ticket.client_name;
                document.getElementById('modal-dest-label').innerText = destLabel;
                destElement.innerText = destName; 

                alertModal.classList.remove('opacity-0', 'pointer-events-none');
                alertModalContent.classList.remove('scale-90');
                alertModalContent.classList.add('scale-100');

                let audioPlayer = (isVIP && vipChimeSound) ? vipChimeSound : chimeSound;
                
                audioPlayer.onended = null;
                audioPlayer.currentTime = 0; 
                
                window.speechSynthesis.cancel(); 

                let script = "";
                if (isVIP) {
                    script = `Cliente ${ticket.client_name}, favor de pasar ${ticket.service_type === 'CASHIER' ? 'a caja principal' : 'con ' + destName}.`;
                } else {
                    let ticketNumber = ticket.turn_number ? ticket.turn_number : '0';
                    let cleanNumber = ticketNumber;
                    if(ticketNumber.includes('-')) {
                        let parts = ticketNumber.split('-');
                        cleanNumber = parseInt(parts[1], 10); 
                    }
                    script = `Turno número ${cleanNumber}. Cliente ${ticket.client_name}, favor de pasar ${ticket.service_type === 'CASHIER' ? 'a caja principal' : 'con el vendedor ' + destName}.`;
                }
                
                const utterance = new SpeechSynthesisUtterance(script);
                if (spanishVoice) utterance.voice = spanishVoice;
                utterance.rate = 0.85; 

                let isModalClosed = false;
                let fallbackTimer = null;

                const closeAlert = () => {
                    if (isModalClosed) return;
                    isModalClosed = true;
                    clearTimeout(fallbackTimer); 
                    
                    alertModal.classList.add('opacity-0', 'pointer-events-none');
                    alertModalContent.classList.remove('scale-100');
                    alertModalContent.classList.add('scale-90');
                    
                    setTimeout(() => {
                        onCompleteCallback(); 
                    }, 500); 
                };

                const startSpeech = () => {
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.speak(utterance);
                    } else {
                        setTimeout(closeAlert, 6000);
                    }
                };

                audioPlayer.onended = startSpeech;

                audioPlayer.play().catch(e => {
                    console.log("Se requiere clic inicial para audio.");
                    startSpeech();
                });

                utterance.onend = () => { setTimeout(closeAlert, 1000); };
                utterance.onerror = () => { closeAlert(); };

                fallbackTimer = setTimeout(closeAlert, 15000); 
            }

            fetchQueueData(); 
            setInterval(fetchQueueData, 3000); 

            playAd(currentAdIndex);
        });
    </script>
</body>
</html>