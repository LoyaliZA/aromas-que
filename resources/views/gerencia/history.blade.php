<x-gerencia-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Historial de Resguardos</h1>
        <p class="text-aromas-tertiary text-sm">Consulta el archivo histórico de paquetería.</p>
    </div>

    {{-- CONTEXTO DE ALPINE JS PARA AJAX --}}
    <div x-data="{
        search: '{{ request('search') }}',
        status: '{{ request('status', 'ALL') }}',
        department: '{{ request('department', 'ALL') }}',
        date_start: '{{ request('date_start') }}',
        date_end: '{{ request('date_end') }}',
        isLoading: false,

        // Función Principal: Fetch de datos sin recargar
        async fetchResults(url = null) {
            this.isLoading = true;
            
            // Si no pasamos URL (ej. paginación), construimos la query actual
            if (!url) {
                const params = new URLSearchParams({
                    search: this.search,
                    status: this.status,
                    department: this.department,
                    date_start: this.date_start,
                    date_end: this.date_end
                });
                url = `{{ route('gerencia.history') }}?${params.toString()}`;
            }

            try {
                // Solicitamos al servidor con cabecera AJAX
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                
                // Reemplazamos el contenedor de la tabla
                document.getElementById('table-container').innerHTML = html;
                
                // Actualizamos la URL del navegador sin recargar (para poder copiar y pegar el link)
                window.history.pushState({}, '', url);
            } catch (error) {
                console.error('Error cargando historial:', error);
            } finally {
                this.isLoading = false;
            }
        }
    }">

        {{-- BARRA DE HERRAMIENTAS --}}
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-3 border border-aromas-tertiary/20 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-3">
                
                {{-- 1. BUSCADOR (Live Search) --}}
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-9 pr-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight placeholder-gray-500 transition-all"
                        placeholder="Buscar por folio, cliente...">
                    
                    {{-- Spinner de carga --}}
                    <div x-show="isLoading" class="absolute right-3 top-2.5">
                        <svg class="animate-spin h-4 w-4 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Separador --}}
                <div class="hidden md:block w-px h-8 bg-aromas-tertiary/20"></div>

                {{-- 2. FILTRO DEPARTAMENTO (NUEVO) --}}
                <div class="w-full md:w-auto">
                    <select x-model="department" @change="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Área: Todas</option>
                        <option value="AROMAS">🟣 Aromas</option>
                        <option value="BELLAROMA">🌸 Bellaroma</option>
                    </select>
                </div>

                {{-- 3. FILTRO ESTADO --}}
                <div class="w-full md:w-auto">
                    <select x-model="status" @change="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Estado: Todos</option>
                        <option value="IN_CUSTODY">📦 En Custodia</option>
                        <option value="DELIVERED">✅ Entregados</option>
                    </select>
                </div>

                {{-- 4. RANGO FECHAS --}}
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <input type="date" x-model="date_start" @change="fetchResults()"
                        class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-2 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight w-full md:w-auto">
                    <span class="text-gray-500">-</span>
                    <input type="date" x-model="date_end" @change="fetchResults()"
                        class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-2 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight w-full md:w-auto">
                </div>

                {{-- Botón Limpiar --}}
                <button x-show="search || status !== 'ALL' || department !== 'ALL' || date_start || date_end" 
                        @click="search=''; status='ALL'; department='ALL'; date_start=''; date_end=''; fetchResults()"
                        class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors" title="Limpiar Filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        {{-- CONTENEDOR DE LA TABLA (Aquí se inyecta el HTML parcial) --}}
        <div id="table-container" class="transition-opacity duration-200" :class="isLoading ? 'opacity-50' : 'opacity-100'">
            @include('gerencia.partials.history-table')
        </div>

    </div>
</x-gerencia-layout>