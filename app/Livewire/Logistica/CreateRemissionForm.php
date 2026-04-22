<?php

namespace App\Livewire\Logistica;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Pickup;
use App\Models\PickupLogistic;
use App\Models\PickupTimeLine;
use App\Models\PickupStatus;
use App\Models\Bank;
use App\Models\Warehouse;
use App\Models\Courier;
use App\Models\BoxType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateRemissionForm extends Component
{
    public $ticket_folio;
    public $capture_date; 
    public $amount;
    public $balance = 0;
    public $pieces;
    public $bank_id = '';
    public $warehouse_id = '';
    public $box_type_id = '';
    public $custom_box_number = '';

    public $delivery_type = ''; 
    public $courier_id = '';
    public $local_courier_name = '';
    public $delivery_address = '';

    public $seller_id = '';
    public $notes;

    public $clientSearch = '';
    public $selectedCustomer = null;
    public $customersList = [];

    public $sellersList = [];
    public $origin;

    public function mount($origin)
    {
        $this->origin = strtoupper($origin);
        $this->capture_date = now()->format('Y-m-d\TH:i');
        $this->sellersList = Employee::where('department', $this->origin)->get();
    }

    public function updatedClientSearch($value)
    {
        $searchTerm = trim($value);
        
        if (strlen($searchTerm) >= 2 || (is_numeric($searchTerm) && strlen($searchTerm) >= 1)) {
            $this->customersList = Customer::where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('customer_number', $searchTerm) // <-- AHORA BUSCA POR EL NÚMERO DE CLIENTE
                ->limit(5)->get();
        } else {
            $this->customersList = [];
        }
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomer = Customer::find($id);
        $this->clientSearch = '';
        $this->customersList = [];
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->clientSearch = '';
    }

    protected function rules()
    {
        return [
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'capture_date' => 'required|date',
            'selectedCustomer' => 'required',
            'pieces' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'balance' => 'nullable|numeric|min:0',
            'bank_id' => 'required|exists:banks,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'box_type_id' => 'required',
            'custom_box_number' => 'required_if:box_type_id,custom',
            'delivery_type' => 'required|in:SHIPPING,LOCAL,STORE',
            'courier_id' => 'required_if:delivery_type,SHIPPING',
            'seller_id' => 'required|exists:employees,id',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Lógica escalable para cajas personalizadas
            $finalBoxTypeId = $this->box_type_id;
            if ($this->box_type_id === 'custom') {
                $newBoxType = BoxType::firstOrCreate(
                    ['name' => $this->custom_box_number],
                    ['description' => $this->custom_box_number . ' Cajas', 'is_active' => true]
                );
                $finalBoxTypeId = $newBoxType->id;
            }

            $capturedStatus = PickupStatus::where('code', 'CAPTURED')->first();

            $pickup = Pickup::create([
                'ticket_folio' => $this->ticket_folio,
                'ticket_date' => $this->capture_date,
                'client_name' => $this->selectedCustomer->name,
                'client_ref_id' => $this->selectedCustomer->id,
                'department' => $this->origin,
                'amount' => $this->amount,
                'balance' => $this->balance ?: 0,
                'pieces' => $this->pieces,
                'box_type_id' => $finalBoxTypeId,
                'status_id' => $capturedStatus->id,
                'warehouse_id' => $this->warehouse_id,
                'bank_id' => $this->bank_id,
                'seller_id' => $this->seller_id,
                'notes' => $this->notes,
            ]);

            PickupLogistic::create([
                'pickup_id' => $pickup->id,
                'courier_id' => $this->delivery_type === 'SHIPPING' ? $this->courier_id : null,
                'local_courier_name' => $this->delivery_type === 'LOCAL' ? $this->local_courier_name : null,
                'delivery_address' => $this->delivery_type === 'LOCAL' ? $this->delivery_address : null,
                'is_store_pickup' => $this->delivery_type === 'STORE',
            ]);

            PickupTimeLine::create([
                'pickup_id' => $pickup->id,
                'user_id' => Auth::id(),
                'status' => $capturedStatus->name, // Corrección: Usamos el nombre en texto para el historial
                'comment' => 'Remisión registrada y en espera de CEDIS.',
                'created_at' => $this->capture_date,
            ]);

            DB::commit();

            session()->flash('success', 'Remisión folio ' . $this->ticket_folio . ' registrada con éxito.');
            return redirect()->route(strtolower($this->origin) . '.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar el registro: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $banksQuery = Bank::where('is_active', true)->orderBy('name', 'asc');

        if ($this->origin === 'BELLAROMA') {
            $banksQuery->whereIn('name', ['Banorte', 'BBVA HV', 'BANORTE', 'BBVA']);
        } elseif ($this->origin === 'CALLCENTER') {
            $banksQuery->whereIn('name', ['BBVA JW', 'BBVA HV', 'BBVA']);
        }

        return view('livewire.logistica.create-remission-form', [
            'banks' => $banksQuery->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name', 'asc')->get(),
            'boxTypes' => BoxType::where('is_active', true)->get(),
            'couriers' => Courier::where('is_active', true)->orderBy('name', 'asc')->get(),
        ]);
    }
}