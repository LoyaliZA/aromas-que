<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyShift;
use App\Models\SalesQueue;
use App\Models\ShiftStatusLog;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * VISTA PRINCIPAL: El Dashboard del Vendedor.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Validar que tenga perfil de empleado
        if (!$user->employee) {
            abort(403, 'Usuario sin perfil de empleado asignado.');
        }

        // 2. Obtener o Crear el Turno de Hoy (Lazy Creation)
        // Si es la primera vez que entra hoy, le creamos su registro en OFFLINE.
        $shift = DailyShift::firstOrCreate(
            [
                'employee_id' => $user->employee->id,
                'work_date'   => today(),
            ],
            [
                'current_status' => 'OFFLINE',
                'customers_served_count' => 0,
                'last_status_change_at' => now(),
                'last_action_at' => now(),
            ]
        );

        // 3. Revisar si ya está atendiendo a alguien (Persistencia al recargar página)
        $currentClient = SalesQueue::where('assigned_shift_id', $shift->id)
                                   ->where('status', 'SERVING')
                                   ->first();

        return view('ventas.dashboard', compact('shift', 'currentClient'));
    }

    /**
     * CAMBIAR ESTADO: Online, Offline, Break.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:ONLINE,OFFLINE,BREAK',
            'break_reason' => 'nullable|in:BATHROOM,LUNCH,ERRAND,PACKAGING', // Validamos los motivos
        ]);

        $user = Auth::user();
        $shift = DailyShift::where('employee_id', $user->employee->id)
                           ->whereDate('work_date', today())
                           ->firstOrFail();

        // Evitar cambios innecesarios
        if ($shift->current_status === $request->status && $shift->break_reason === $request->break_reason) {
            return response()->json(['status' => 'no_change']);
        }

        // Registrar en Bitácora (Log)
        ShiftStatusLog::create([
            'daily_shift_id' => $shift->id,
            'previous_status' => $shift->current_status,
            'new_status' => $request->status,
            'changed_at' => now(),
        ]);

        // Actualizar Turno
        $shift->current_status = $request->status;
        $shift->break_reason = ($request->status === 'BREAK') ? $request->break_reason : null; // Limpiar razón si no es break
        
        // REGLA CLAVE: Si se pone ONLINE, reiniciamos su contador de espera (para la cola de justicia)
        // A menos que venga de atender un cliente (eso se maneja en finish),
        // pero si viene de Break/Offline, entra al final de la fila de vendedores.
        if ($request->status === 'ONLINE') {
            $shift->last_status_change_at = now();
        }

        $shift->last_action_at = now(); // Heartbeat
        $shift->save();

        return response()->json([
            'success' => true,
            'new_status' => $shift->current_status,
            'message' => 'Estado actualizado.'
        ]);
    }

    /**
     * HEARTBEAT / POLLING: El cerebro de la asignación automática.
     * Esta función será llamada por JS cada X segundos.
     */
    public function checkQueue(Request $request)
    {
        $user = Auth::user();
        $shift = DailyShift::where('employee_id', $user->employee->id)
                           ->whereDate('work_date', today())
                           ->first();

        if (!$shift) return response()->json(['status' => 'error'], 404);

        // Actualizamos "Heartbeat" para saber que sigue vivo
        $shift->touchLastAction();

        // CASO 1: YA ESTÁ ATENDIENDO (Recuperación de estado)
        $activeClient = SalesQueue::where('assigned_shift_id', $shift->id)
                                  ->where('status', 'SERVING')
                                  ->first();

        if ($activeClient) {
            return response()->json([
                'status' => 'serving',
                'client' => $activeClient
            ]);
        }

        // CASO 2: NO ESTÁ ONLINE (No asignar nada)
        if ($shift->current_status !== 'ONLINE') {
            return response()->json(['status' => 'offline_or_break']);
        }

        // CASO 3: ESTÁ ONLINE Y LIBRE -> INTENTAR ASIGNAR (MATCH)
        // Usamos una transacción para evitar "Race Conditions" (que 2 vendedores tomen al mismo)
        $assignment = DB::transaction(function () use ($shift) {
            
            // A. ¿Soy el elegido? (Lógica del Modelo DailyShift)
            $nextAgent = DailyShift::assignNextAgent();

            // Si no soy yo el siguiente en la lista de méritos, espero.
            if (!$nextAgent || $nextAgent->id !== $shift->id) {
                return null;
            }

            // B. ¿Hay clientes esperando en VENTAS? (Ignoramos los de CAJA)
            // Usamos lockForUpdate para bloquear la fila mientras leemos
            $nextClient = SalesQueue::waiting()
                                    ->sales() // Scope que creamos (service_type = SALES)
                                    ->lockForUpdate()
                                    ->first();

            if ($nextClient) {
                // C. ¡MATCH! Asignar cliente al vendedor
                $nextClient->update([
                    'status' => 'SERVING',
                    'assigned_shift_id' => $shift->id,
                    'started_serving_at' => now(),
                ]);
                return $nextClient;
            }

            return null;
        });

        if ($assignment) {
            return response()->json([
                'status' => 'assigned',
                'client' => $assignment
            ]);
        }

        // CASO 4: ONLINE PERO SIN CLIENTES (ESPERANDO)
        return response()->json(['status' => 'waiting']);
    }

    /**
     * FINALIZAR ATENCIÓN: Cerrar ticket y volver a la cola.
     */
    public function finishService(Request $request)
    {
        $user = Auth::user();
        $shift = DailyShift::where('employee_id', $user->employee->id)
                           ->whereDate('work_date', today())
                           ->firstOrFail();

        // Buscar al cliente que estaba atendiendo
        $client = SalesQueue::where('assigned_shift_id', $shift->id)
                            ->where('status', 'SERVING')
                            ->first();

        if ($client) {
            $client->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
            ]);

            // Actualizar estadísticas del vendedor
            $shift->increment('customers_served_count');
            
            // IMPORTANTE: Al terminar, el vendedor queda ONLINE pero "al final de la fila"
            // para recibir clientes (Round Robin justo).
            // Actualizamos su timestamp para que DailyShift::assignNextAgent() lo ponga al final.
            $shift->update([
                'last_status_change_at' => now(),
                'last_action_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }
}