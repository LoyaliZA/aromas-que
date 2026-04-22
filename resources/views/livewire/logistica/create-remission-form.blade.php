<div class="bg-aromas-secondary border border-aromas-tertiary/50 rounded-xl shadow-sm p-6 max-w-4xl mx-auto relative">

    @if (session()->has('info'))
    <div class="mb-6 bg-blue-500/20 border-l-4 border-blue-500 text-white p-4 rounded shadow-sm">
        {{ session('info') }}
    </div>
    @endif
    @if (session()->has('error'))
    <div class="mb-6 bg-red-500/20 border-l-4 border-red-500 text-white p-4 rounded shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-8">

        <div class="border-b border-aromas-tertiary/30 pb-6">
            <h3 class="text-lg font-medium text-white mb-4">1. Datos del Cliente</h3>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-300 mb-1">Buscar Cliente (No. o Nombre)</label>

                @if($selectedCustomer)
                <div class="flex items-center justify-between bg-aromas-success/10 border border-aromas-success/30 rounded-lg p-4">
                    <div>
                        <p class="text-aromas-success font-bold text-lg">{{ $selectedCustomer->name }}</p>
                        <p class="text-xs text-gray-400">No. Cliente: {{ $selectedCustomer->customer_number }}</p>
                    </div>
                    <button type="button" wire:click="clearCustomer" class="bg-red-500/20 hover:bg-red-500 hover:text-white text-red-400 px-3 py-1 rounded transition-colors text-sm font-bold">
                        Cambiar Cliente
                    </button>
                </div>
                @else
                <div class="flex gap-2">
                    <input type="text" wire:model.live.debounce.300ms="clientSearch"
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight transition-colors"
                        placeholder="Escribe el nombre o número de cliente...">
                </div>

                @php
                $searchTrimmed = trim($clientSearch);
                $showResultsBox = (is_numeric($searchTrimmed) && strlen($searchTrimmed) >= 1) || (!is_numeric($searchTrimmed) && strlen($searchTrimmed) > 2);
                @endphp

                @if($showResultsBox)
                <div class="absolute z-10 w-full mt-1 bg-gray-900 border border-aromas-tertiary rounded-lg shadow-xl max-h-60 overflow-y-auto">
                    @if(count($customersList) > 0)
                    <ul class="divide-y divide-aromas-tertiary/30">
                        @foreach($customersList as $customer)
                        <li>
                            <button type="button" wire:click="selectCustomer({{ $customer->id }})" class="w-full text-left px-4 py-3 hover:bg-aromas-tertiary/20 transition-colors">
                                <p class="text-white font-medium">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-400">No: {{ $customer->customer_number }}</p>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="p-6 text-center">
                        <p class="text-gray-400 mb-3">No se encontró al cliente en la base de datos.</p>
                    </div>
                    @endif
                </div>
                @endif
                @endif
                @error('selectedCustomer') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="border-b border-aromas-tertiary/30 pb-6">
            <h3 class="text-lg font-medium text-white mb-4">2. Información del Pedido</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Fecha y Hora de Captura</label>
                    <input type="datetime-local" wire:model="capture_date" required
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                    @error('capture_date') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Número de Remisión</label>
                    <input type="text" wire:model="ticket_folio" required
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="Ej. REM-10293">
                    @error('ticket_folio') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Monto de Nota ($)</label>
                    <input type="number" step="0.01" wire:model="amount" required
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="0.00">
                    @error('amount') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Saldo a Favor ($)</label>
                    <input type="number" step="0.01" wire:model="balance"
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="0.00 (Opcional)">
                    @error('balance') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Total de Piezas (Fragancias)</label>
                    <input type="number" wire:model="pieces" required min="1"
                        class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="Cantidad de perfumes">
                    @error('pieces') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-data="{ boxType: @entangle('box_type_id') }">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Número de Cajas</label>
                    <div class="flex gap-2">
                        <select wire:model.live="box_type_id" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                            <option value="">-- Seleccionar --</option>
                            @foreach($boxTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                            <option value="custom">Otro (Especificar)</option>
                        </select>
                        <input x-show="boxType === 'custom'" type="text" wire:model="custom_box_number" x-cloak
                            class="w-1/2 bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60" placeholder="Ej. 5">
                    </div>
                    @error('box_type_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    @error('custom_box_number') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Banco de Pago</label>
                    <select wire:model="bank_id" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                        <option value="">-- Seleccionar Banco --</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    @error('bank_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Almacén de Origen</label>
                    <select wire:model="warehouse_id" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                        <option value="">-- Seleccionar Almacén --</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="border-b border-aromas-tertiary/30 pb-6" x-data="{ deliveryMethod: @entangle('delivery_type') }">
            <h3 class="text-lg font-medium text-white mb-4">3. Logística y Envío</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <label class="relative flex flex-col p-4 border border-aromas-tertiary rounded-lg cursor-pointer hover:bg-aromas-tertiary/20 transition-colors"
                    :class="deliveryMethod === 'SHIPPING' ? 'bg-aromas-tertiary/30 border-aromas-highlight ring-1 ring-aromas-highlight' : 'bg-black/40'">
                    <input type="radio" wire:model.live="delivery_type" value="SHIPPING" class="sr-only">
                    <span class="text-white font-bold text-center">Envío por Paquetería</span>
                </label>

                <label class="relative flex flex-col p-4 border border-aromas-tertiary rounded-lg cursor-pointer hover:bg-aromas-tertiary/20 transition-colors"
                    :class="deliveryMethod === 'LOCAL' ? 'bg-aromas-tertiary/30 border-aromas-highlight ring-1 ring-aromas-highlight' : 'bg-black/40'">
                    <input type="radio" wire:model.live="delivery_type" value="LOCAL" class="sr-only">
                    <span class="text-white font-bold text-center">Envío Local (Personalizado)</span>
                </label>

                <label class="relative flex flex-col p-4 border border-aromas-tertiary rounded-lg cursor-pointer hover:bg-aromas-tertiary/20 transition-colors"
                    :class="deliveryMethod === 'STORE' ? 'bg-aromas-tertiary/30 border-aromas-highlight ring-1 ring-aromas-highlight' : 'bg-black/40'">
                    <input type="radio" wire:model.live="delivery_type" value="STORE" class="sr-only">
                    <span class="text-white font-bold text-center">Recoge en Tienda</span>
                </label>
            </div>
            @error('delivery_type') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror

            <div x-show="deliveryMethod === 'SHIPPING'" x-collapse x-cloak>
                <div class="p-4 bg-black/20 border border-aromas-tertiary/50 rounded-lg">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Paquetería Designada</label>
                    <select wire:model="courier_id" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                        <option value="">-- Seleccionar Paquetería --</option>
                        @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                        @endforeach
                    </select>
                    @error('courier_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div x-show="deliveryMethod === 'LOCAL'" x-collapse x-cloak>
                <div class="p-4 bg-black/20 border border-aromas-tertiary/50 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">¿Quién realiza la entrega?</label>
                        <input type="text" wire:model="local_courier_name" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="Ej. Repartidor Juan">
                        @error('local_courier_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Dirección / Destino</label>
                        <input type="text" wire:model="delivery_address" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="Ej. Calle 123, Colonia Centro">
                        @error('delivery_address') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Vendedora que realizó la venta</label>
                <select wire:model="seller_id" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight">
                    <option value="">-- Seleccionar Vendedora --</option>
                    @foreach($sellersList as $seller)
                    <option value="{{ $seller->id }}">{{ $seller->full_name }}</option>
                    @endforeach
                </select>
                @error('seller_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Comentarios / Observaciones</label>
                <textarea wire:model="notes" rows="2" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight focus:bg-black/60 focus:border-aromas-highlight" placeholder="Añade detalles sobre el pedido o condiciones especiales..."></textarea>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-bold py-3 px-8 rounded-lg shadow-lg shadow-yellow-500/30 transition-all flex items-center">
                <span wire:loading wire:target="save" class="mr-2">
                    <svg class="animate-spin h-5 w-5 text-aromas-main" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="save">Capturar Remisión</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>