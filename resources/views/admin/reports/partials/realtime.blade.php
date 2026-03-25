<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest flex items-center gap-2">
        <span class="relative flex h-4 w-4"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span></span>
        Monitor en Vivo
    </h3>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <template x-for="seller in realTimeData" :key="seller.id">
        <div class="bg-gray-900 rounded-xl border p-5 shadow-lg relative overflow-hidden transition-all duration-300"
            :class="{'border-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.2)]': seller.state === 'SERVING', 'border-green-500/50': seller.state === 'ONLINE', 'border-yellow-500/50 opacity-80': seller.state === 'BREAK', 'border-gray-700 opacity-50': seller.state === 'OFFLINE'}">
            
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h4 class="text-lg font-bold text-white" x-text="seller.name"></h4>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded mt-1 inline-block"
                        :class="{'bg-blue-900/50 text-blue-400': seller.state === 'SERVING', 'bg-green-900/50 text-green-400': seller.state === 'ONLINE', 'bg-yellow-900/50 text-yellow-400': seller.state === 'BREAK', 'bg-gray-800 text-gray-500': seller.state === 'OFFLINE'}"
                        x-text="seller.state === 'SERVING' ? 'Atendiendo' : (seller.state === 'ONLINE' ? 'Disponible' : (seller.state === 'BREAK' ? 'En Pausa' : 'Inactivo'))"></span>
                </div>
            </div>

            <div class="space-y-4">
                <div x-show="seller.state !== 'OFFLINE'" class="bg-black/30 rounded-lg p-3 border border-gray-800 flex justify-between items-center">
                    <span class="text-xs text-gray-500 uppercase font-bold" x-text="seller.state === 'ONLINE' ? 'Libre desde hace:' : 'Tiempo Transcurrido'"></span>
                    <span class="text-xl font-mono font-black" :class="{'text-blue-400': seller.state === 'SERVING', 'text-green-400': seller.state === 'ONLINE', 'text-yellow-400': seller.state === 'BREAK'}" x-text="formatTimer(seller.state_started_at)"></span>
                </div>
                
                <div class="flex justify-between items-center pt-2 border-t border-gray-800">
                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Llegada: <strong class="text-gray-300" x-text="seller.shift_started_at || '--:--'"></strong></span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider">Ventas hoy: <strong class="text-white" x-text="seller.sales_today"></strong></span>
                </div>
            </div>
        </div>
    </template>
    
    <div x-show="realTimeData.length === 0" class="col-span-full text-center py-12 text-gray-500 font-bold">
        Cargando información en vivo...
    </div>
</div>