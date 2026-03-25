<!DOCTYPE html>
<html lang="es-MX" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Califica tu Experiencia - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white font-sans antialiased h-screen w-screen overflow-hidden flex flex-col" x-data="clientRatingApp()">

    {{-- HEADER FLOTANTE --}}
    <header class="bg-gray-900/90 backdrop-blur-md border-b border-gray-800 p-4 shrink-0 flex justify-center items-center">
        <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Aromas Logo" class="h-8 w-auto object-contain">
    </header>

    {{-- ÁREA PRINCIPAL DINÁMICA --}}
    <main class="flex-1 overflow-y-auto p-4 sm:p-8 flex flex-col justify-center items-center relative">
        
        {{-- PANTALLA 1: SELECCIÓN DEL GERENTE --}}
        <div x-show="step === 'SELECT_SALE'" class="w-full max-w-2xl" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-black text-blue-400 uppercase tracking-widest">Seleccionar Venta</h2>
                <button @click="fetchSales()" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></button>
            </div>
            
            <div class="space-y-3 overflow-y-auto max-h-[70vh] pr-2 custom-scrollbar">
                <div x-show="sales.length === 0" class="text-center py-10 text-gray-500 font-bold">Buscando ventas recientes...</div>
                
                <template x-for="sale in sales" :key="sale.id">
                    <button @click="startRating(sale)" class="w-full text-left bg-gray-800 border border-gray-700 hover:border-blue-500 rounded-xl p-4 flex justify-between items-center transition-all active:scale-95">
                        <div>
                            <span class="text-white font-bold text-lg block" x-text="sale.client_name"></span>
                            <span class="text-sm text-gray-400">Atendió: <strong x-text="sale.assigned_shift ? sale.assigned_shift.employee.full_name : 'N/A'"></strong></span>
                        </div>
                        <span class="bg-blue-900/30 text-blue-400 px-3 py-1 rounded-lg text-sm font-mono font-bold" x-text="sale.turn_number"></span>
                    </button>
                </template>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('gerencia.dashboard') }}" class="text-gray-500 text-sm font-bold uppercase underline">Volver a Gerencia</a>
            </div>
        </div>

        {{-- PANTALLA 2: CALIFICACIÓN DEL CLIENTE --}}
        <div x-show="step === 'RATING'" class="w-full max-w-xl bg-gray-800/50 rounded-3xl border border-gray-700 p-6 sm:p-10 shadow-2xl backdrop-blur-sm" x-transition style="display: none;">
            <h1 class="text-3xl sm:text-4xl font-black text-white text-center mb-2 leading-tight">¿Cómo fue tu experiencia hoy?</h1>
            <p class="text-gray-400 text-center mb-8 sm:text-lg">Tu opinión nos ayuda a mejorar nuestro servicio.</p>

            {{-- Estrellas --}}
            <div class="flex justify-center gap-3 sm:gap-4 mb-8">
                <template x-for="star in 5" :key="star">
                    <button type="button" @click="ratingStars = star" class="focus:outline-none transition-transform hover:scale-110 active:scale-90">
                        {{-- Estilos inline agregados como respaldo por si Tailwind no está compilando en tiempo real --}}
                        <svg style="min-width: 3.5rem; min-height: 3.5rem;" class="w-14 h-14 sm:w-20 sm:h-20 transition-colors drop-shadow-lg" :class="ratingStars >= star ? 'text-yellow-400' : 'text-gray-500'" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                </template>
            </div>

            {{-- Tags --}}
            <div x-show="ratingStars > 0" class="flex flex-wrap justify-center gap-2 sm:gap-3 mb-8" x-transition>
                <template x-for="tag in availableTags()" :key="tag">
                    <button @click="toggleTag(tag)" 
                            :class="ratingTags.includes(tag) ? 'bg-blue-600 text-white border-blue-500 shadow-lg' : 'bg-gray-900 text-gray-400 border-gray-700'"
                            class="px-4 py-2 border-2 rounded-full text-sm sm:text-base font-bold transition-all uppercase tracking-wider" x-text="tag"></button>
                </template>
            </div>

            <div x-show="ratingStars > 0" x-transition>
                <textarea x-model="ratingComment" class="w-full bg-gray-900 border-2 border-gray-700 rounded-xl p-4 text-white focus:border-blue-500 text-base mb-8 resize-none" rows="3" placeholder="¿Algo más que nos quieras compartir? (Opcional)"></textarea>

                <button @click="submitRating()" class="w-full py-4 bg-blue-600 hover:bg-blue-500 rounded-xl text-white text-lg font-black shadow-[0_0_20px_rgba(37,99,235,0.4)] transition-transform active:scale-95 uppercase tracking-widest" :disabled="isSubmitting">
                    <span x-text="isSubmitting ? 'Enviando...' : 'Enviar Calificación'"></span>
                </button>
            </div>
        </div>

        {{-- PANTALLA 3: AGRADECIMIENTO --}}
        <div x-show="step === 'THANK_YOU'" class="w-full max-w-md text-center" x-transition style="display: none;">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-500/20 text-green-400 mb-6">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-4xl font-black text-white mb-4 uppercase tracking-widest">¡Gracias!</h1>
            <p class="text-gray-400 text-lg mb-12">Tus respuestas han sido enviadas exitosamente. Vuelve pronto.</p>
            
            {{-- Botón discreto para que el gerente reinicie la tablet --}}
            <button @click="resetApp()" class="px-6 py-2 rounded-full border border-gray-700 text-gray-500 text-xs uppercase font-bold hover:bg-gray-800">
                Gerencia: Nueva Calificación
            </button>
        </div>

    </main>

    <script>
        function clientRatingApp() {
            return {
                step: 'SELECT_SALE', // SELECT_SALE, RATING, THANK_YOU
                sales: [],
                selectedSale: null,
                
                // Formulario
                ratingStars: 0,
                ratingTags: [],
                ratingComment: '',
                isSubmitting: false,

                init() {
                    this.fetchSales();
                },

                fetchSales() {
                    fetch("{{ route('gerencia.calificacion.recent') }}", {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => { this.sales = data.sales; });
                },

                startRating(sale) {
                    this.selectedSale = sale;
                    this.ratingStars = 0;
                    this.ratingTags = [];
                    this.ratingComment = '';
                    this.step = 'RATING';
                },

                availableTags() {
                    if (this.ratingStars >= 4) return ['Excelente Actitud', 'Servicio Rápido', 'Resolvió mis dudas', 'Muy Amable'];
                    if (this.ratingStars === 3) return ['Servicio Regular', 'Tiempo de espera largo', 'Atención promedio'];
                    if (this.ratingStars > 0) return ['Mala Actitud', 'Muy Lento', 'No resolvió mi problema', 'Distraído'];
                    return [];
                },

                toggleTag(tag) {
                    if (this.ratingTags.includes(tag)) {
                        this.ratingTags = this.ratingTags.filter(t => t !== tag);
                    } else {
                        this.ratingTags.push(tag);
                    }
                },

                submitRating() {
                    if (this.ratingStars === 0 || this.isSubmitting) return;
                    this.isSubmitting = true;

                    fetch("{{ route('gerencia.calificacion.store') }}", {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                            'Content-Type': 'application/json' 
                        },
                        body: JSON.stringify({
                            queue_id: this.selectedSale.id,
                            stars: this.ratingStars,
                            tags: this.ratingTags,
                            comments: this.ratingComment
                        })
                    }).then(r => r.json()).then(data => {
                        this.isSubmitting = false;
                        if(data.success) {
                            this.step = 'THANK_YOU';
                        }
                    }).catch(() => {
                        this.isSubmitting = false;
                        alert("Ocurrió un error al enviar la calificación.");
                    });
                },

                resetApp() {
                    this.step = 'SELECT_SALE';
                    this.fetchSales();
                }
            }
        }
    </script>
</body>
</html>