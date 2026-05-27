<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pickup;
use App\Models\PickupEdit;
use App\Models\PickupStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\SalesQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Support\CustodyDurationFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PickupController extends Controller
{

    /**
     * DASHBOARD: Panel de Control Global de Gerencia
     */
    public function index()
    {
        $today = today();

        // 1. Métricas de Atención en Piso
        $queueMetrics = [
            'waiting' => SalesQueue::whereDate('queued_at', $today)->where('status', 'WAITING')->count(),
            'serving' => SalesQueue::whereDate('queued_at', $today)->where('status', 'SERVING')->count(),
            'completed' => SalesQueue::whereDate('queued_at', $today)->where('status', 'COMPLETED')->count(),
            'abandoned' => SalesQueue::whereDate('queued_at', $today)->whereIn('status', ['ABANDONED', 'CANCELED'])->count(),
        ];

        // 2. Control de Personal Activo (Vendedores)
        $sellers = Employee::sellers()
            ->with(['user', 'todayShift'])
            ->get();

        // 3. Lista de Clientes en Espera (Ordenados por prioridad y llegada)
        $waitingClients = SalesQueue::today()->waiting()->get();

        return view('gerencia.dashboard', compact('queueMetrics', 'sellers', 'waitingClients'));
    }

   /**
     * OPERACIÓN DIARIA: Tabla de trabajo con Filtros y Modales.
     * MODIFICADO: Excluye rezagados (>15 días) e incluye folios pendientes de días anteriores.
     */
    public function daily(Request $request)
    {
        $todaysPickups = $this->getOrderedDailyPickups($request);
        $widgetCounts = $this->computeWidgetCounts(
            $this->getDailyPickupsForWidgets($request)
        );

        if ($request->ajax()) {
            return view('gerencia.partials.daily-table', compact('todaysPickups'))->render();
        }

        return view('gerencia.daily', compact('todaysPickups', 'widgetCounts'));
    }

    /**
     * Métricas JSON para alertas sonoras/TTS en Operación Diaria.
     */
    public function dailyStats(Request $request)
    {
        $pickups = $this->getOrderedDailyPickups($request);
        $widgetPickups = $this->getDailyPickupsForWidgets($request);
        $todayStart = today()->startOfDay();

        $pending = $pickups->filter(
            fn (Pickup $p) => $p->currentStatus?->code === 'PENDING_CONFIRMATION'
        );

        $canManageRezagados = Auth::user()->canManageRezagados();
        $rezagadosCount = $canManageRezagados ? Pickup::rezagados()->count() : 0;

        $stalePending = $pending->filter(
            fn (Pickup $p) => $p->created_at->lt($todayStart)
        );

        return response()->json([
            'pending' => $pending->map(fn (Pickup $p) => [
                'id' => $p->id,
                'folio' => $p->ticket_folio,
                'department' => $p->department,
                'is_stale' => $p->created_at->lt($todayStart),
            ])->values(),
            'counts' => [
                'pending' => $pending->count(),
                'stale_pending' => $stalePending->count(),
                'rezagados' => $rezagadosCount,
            ],
            'widgets' => $this->computeWidgetCounts($widgetPickups),
            'can_manage_rezagados' => $canManageRezagados,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Reporte diario de resguardos (PDF o CSV).
     */
    /** Estatus que aún no pasan auditoría de gerencia. */
    private const UNAUDITED_STATUS_CODES = [
        'PENDING_CONFIRMATION',
        'NEEDS_CORRECTION',
        'PRE_REGISTERED',
    ];

    public function dailyReport(Request $request)
    {
        $format = $request->input('format', 'pdf');
        if (!in_array($format, ['pdf', 'csv'], true)) {
            $format = 'pdf';
        }

        $reportDate = Carbon::parse($request->input('date', today()->toDateString()))->startOfDay();

        $dayPickups = $this->getReportPickupsOrdered(
            $this->getDayPickupsForReport($request, $reportDate),
            'desc'
        );

        $unauditedPickups = $this->getReportPickupsOrdered(
            $this->getUnauditedPickupsForReport($request)
        );

        $priorDaysPickups = $this->getReportPickupsOrdered(
            $this->getPriorDaysOperationalPickupsForReport($request, $reportDate)
        );

        $statusSummary = $dayPickups
            ->groupBy(fn (Pickup $p) => $p->currentStatus->name ?? 'Sin estatus')
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        $fileName = 'Reporte_Resguardos_' . $reportDate->format('Ymd') . '_' . now()->format('His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('gerencia.reports.daily-pickups-pdf', [
                'reportDate' => $reportDate,
                'dayPickups' => $dayPickups,
                'statusSummary' => $statusSummary,
                'unauditedPickups' => $unauditedPickups,
                'priorDaysPickups' => $priorDaysPickups,
                'generatedAt' => now(),
                'managerName' => Auth::user()->name,
            ]);

            return $pdf->download($fileName . '.pdf');
        }

        return $this->streamDailyReportCsv(
            $fileName,
            $reportDate,
            $dayPickups,
            $statusSummary,
            $unauditedPickups,
            $priorDaysPickups
        );
    }

    private function getDayPickupsForReport(Request $request, Carbon $reportDate): Builder
    {
        $query = Pickup::query()
            ->select('pickups.*')
            ->with('currentStatus')
            ->whereDate('pickups.created_at', $reportDate);

        return $this->applyDailyReportFilters($query, $request);
    }

    /**
     * Sin auditar al cierre: incluye pendientes de días anteriores (sin límite de 15 días).
     */
    private function getUnauditedPickupsForReport(Request $request): Builder
    {
        $query = Pickup::query()
            ->select('pickups.*')
            ->with('currentStatus')
            ->whereHas('currentStatus', function ($q) {
                $q->whereIn('code', self::UNAUDITED_STATUS_CODES);
            });

        return $this->applyDailyReportFilters($query, $request);
    }

    /**
     * En resguardo registrados antes del día del reporte (incluye existentes en producción).
     */
    private function getPriorDaysOperationalPickupsForReport(Request $request, Carbon $reportDate): Builder
    {
        $query = Pickup::query()
            ->select('pickups.*')
            ->with('currentStatus')
            ->whereDate('pickups.created_at', '<', $reportDate)
            ->whereHas('currentStatus', function ($q) {
                $q->where('code', 'IN_CUSTODY');
            });

        return $this->applyDailyReportFilters($query, $request);
    }

    private function getReportPickupsOrdered(Builder $query, string $createdAtDirection = 'asc')
    {
        return $query
            ->join('pickup_statuses', 'pickups.status_id', '=', 'pickup_statuses.id')
            ->orderBy('pickup_statuses.sort_order', 'asc')
            ->orderBy('pickups.created_at', $createdAtDirection)
            ->get();
    }

    private function buildDailyBaseQuery(): Builder
    {
        return Pickup::query()
            ->select('pickups.*')
            ->with('currentStatus')
            ->where(function ($q) {
                $q->whereDate('pickups.created_at', today())
                    ->orWhereHas('currentStatus', function ($statusQ) {
                        $statusQ->whereIn('code', self::UNAUDITED_STATUS_CODES);
                    })
                    ->orWhere(function ($subQ) {
                        $subQ->whereHas('currentStatus', function ($statusQ) {
                            $statusQ->where('code', 'IN_CUSTODY');
                        })
                            ->where('pickups.created_at', '>=', now()->subDays(15)->startOfDay());
                    });
            });
    }

    private function applyDailyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->search($request->search)
            ->byStatus($request->status)
            ->byDepartment($request->department);
    }

    /** Filtros del reporte: búsqueda y área (no el estatus activo en la tabla). */
    private function applyDailyReportFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->search($request->search)
            ->byDepartment($request->department);
    }

    private function getOrderedDailyPickups(Request $request)
    {
        $query = $this->buildDailyBaseQuery();
        $this->applyDailyFilters($query, $request);

        return $query
            ->join('pickup_statuses', 'pickups.status_id', '=', 'pickup_statuses.id')
            ->orderBy('pickup_statuses.sort_order', 'asc')
            ->orderBy('pickups.created_at', 'desc')
            ->get();
    }

    /**
     * Colección para widgets (sin filtro de estatus de la tabla).
     */
    private function getDailyPickupsForWidgets(Request $request)
    {
        $query = $this->buildDailyBaseQuery();
        $query->search($request->search)->byDepartment($request->department);

        return $query->get();
    }

    private function computeWidgetCounts($pickups): array
    {
        return [
            'pending_approval' => $pickups->filter(
                fn (Pickup $p) => $p->currentStatus?->code === 'PENDING_CONFIRMATION'
            )->count(),
            'audited' => $pickups->filter(
                fn (Pickup $p) => $p->currentStatus?->code === 'IN_CUSTODY'
            )->count(),
            'delivered' => $pickups->filter(
                fn (Pickup $p) => $p->currentStatus?->code === 'DELIVERED'
            )->count(),
            'pending' => $pickups->filter(
                fn (Pickup $p) => in_array($p->currentStatus?->code, ['NEEDS_CORRECTION', 'PRE_REGISTERED'], true)
            )->count(),
        ];
    }

    private function streamDailyReportCsv(
        string $fileName,
        Carbon $reportDate,
        $dayPickups,
        $statusSummary,
        $unauditedPickups,
        $priorDaysPickups
    ): StreamedResponse {
        $callback = function () use ($reportDate, $dayPickups, $statusSummary, $unauditedPickups, $priorDaysPickups) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['REPORTE DIARIO DE RESGUARDOS - AROMAS']);
            fputcsv($file, ['Fecha del reporte', $reportDate->format('d/m/Y')]);
            fputcsv($file, ['Generado', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, ['RESGUARDOS DEL DÍA']);
            fputcsv($file, ['Folio', 'Cliente', 'Área', 'Piezas', 'Bolsas', 'Estatus', 'Hora', 'Auditado']);

            foreach ($dayPickups as $pickup) {
                fputcsv($file, [
                    $pickup->ticket_folio,
                    $pickup->client_name,
                    $pickup->department,
                    $pickup->pieces,
                    $pickup->bags ?? 0,
                    $pickup->currentStatus->name ?? 'N/A',
                    $pickup->created_at->format('d/m/Y H:i'),
                    $pickup->currentStatus?->code === 'PENDING_CONFIRMATION' ? 'No' : 'Sí',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['RESUMEN POR ESTATUS']);
            fputcsv($file, ['Estatus', 'Cantidad']);
            foreach ($statusSummary as $statusName => $count) {
                fputcsv($file, [$statusName, $count]);
            }

            fputcsv($file, []);
            fputcsv($file, ['NO AUDITADOS AL CIERRE']);
            fputcsv($file, ['Folio', 'Cliente', 'Área', 'Estatus', 'Fecha registro', 'Tiempo en cola']);

            foreach ($unauditedPickups as $pickup) {
                fputcsv($file, [
                    $pickup->ticket_folio,
                    $pickup->client_name,
                    $pickup->department,
                    $pickup->currentStatus->name ?? 'N/A',
                    $pickup->created_at->format('d/m/Y H:i'),
                    CustodyDurationFormatter::inQueue($pickup->created_at),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['EN RESGUARDO — DÍAS ANTERIORES']);
            fputcsv($file, ['Folio', 'Cliente', 'Área', 'Estatus', 'Fecha registro', 'Tiempo en cola']);

            foreach ($priorDaysPickups as $pickup) {
                fputcsv($file, [
                    $pickup->ticket_folio,
                    $pickup->client_name,
                    $pickup->department,
                    $pickup->currentStatus->name ?? 'N/A',
                    $pickup->created_at->format('d/m/Y H:i'),
                    CustodyDurationFormatter::inQueue($pickup->created_at),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}.csv",
        ]);
    }

    /**
     * HISTORIAL: Buscador AJAX.
     */
    public function history(Request $request)
    {
        $query = Pickup::query();

        $query->search($request->search)
            ->byStatus($request->status)
            ->byDepartment($request->department)
            ->byDate($request->date_start, $request->date_end);

        $pickups = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('gerencia.partials.history-table', compact('pickups'))->render();
        }

        return view('gerencia.history', compact('pickups'));
    }

    /**
     * VISTA EXCLUSIVA: REZAGADOS (+15 Días)
     */
    public function rezagados()
    {
        // CORREGIDO: Leemos la propiedad directamente para evitar errores del editor
        if (!Auth::user()->can_manage_rezagados) {
            return redirect()->route('gerencia.dashboard')->with('error', 'No tienes permisos para acceder a la bóveda de rezagados.');
        }

        // Usamos el scope que ya tenías creado en el modelo
        $rezagados = Pickup::rezagados()->orderBy('created_at', 'asc')->get();

        return view('gerencia.rezagados', compact('rezagados'));
    }

    /**
     * ENTREGAR REZAGADO (redirige al flujo unificado de entrega)
     */
    public function entregarRezagado(Request $request, $id)
    {
        return $this->deliver($request, $id);
    }

    /**
     * Confirmar entrega de resguardo (firma, evidencia, titular o tercero).
     */
    public function deliver(Request $request, $id)
    {
        $pickup = Pickup::with('currentStatus')->findOrFail($id);

        if ($pickup->currentStatus?->code !== 'IN_CUSTODY') {
            return redirect()->back()->with('error', 'Solo se pueden entregar resguardos en custodia oficial.');
        }

        $isRezagado = $pickup->created_at->lt(now()->subDays(15)->startOfDay());
        if ($isRezagado && !Auth::user()->canManageRezagados()) {
            return redirect()->back()->with('error', 'No tienes permisos para entregar resguardos rezagados.');
        }

        $request->validate([
            'signature' => 'required|string',
            'is_third_party' => 'nullable|boolean',
            'receiver_name' => 'required_if:is_third_party,1|nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'evidence_file' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $signaturePath = $this->storeSignatureImage($request->signature);
        if (!$signaturePath) {
            return redirect()->back()->with('error', 'La firma no pudo guardarse. Intenta de nuevo.');
        }

        $evidencePath = $request->file('evidence_file')->store('pickups/delivery_evidence', 'public');
        $deliveredStatusId = PickupStatus::where('code', 'DELIVERED')->value('id');
        $isThirdParty = $request->boolean('is_third_party');

        $receiverName = $isThirdParty
            ? $request->receiver_name
            : ($request->input('receiver_name') ?: $pickup->client_name);

        $updateData = [
            'status_id' => $deliveredStatusId,
            'delivered_at' => now(),
            'signature_path' => $signaturePath,
            'is_third_party' => $isThirdParty,
            'receiver_name' => $receiverName,
            'evidence_path' => $evidencePath,
        ];

        if (!$isThirdParty && $request->filled('customer_id')) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $updateData['client_ref_id'] = (string) $customer->id;
                $updateData['client_name'] = $customer->name;
                $receiverName = $customer->name;
                $updateData['receiver_name'] = $receiverName;
            }
        }

        $notes = $pickup->notes ?? '';
        if ($request->filled('notes')) {
            $label = $isRezagado ? '[Gerencia entrega rezago]' : '[Gerencia entrega]';
            $notes = trim($notes . "\n{$label}: " . $request->notes);
            $updateData['notes'] = $notes;
        }

        DB::transaction(function () use ($pickup, $updateData, $isRezagado) {
            $pickup->update($updateData);

            PickupEdit::create([
                'pickup_id' => $pickup->id,
                'user_id' => Auth::id(),
                'changes' => json_encode(['status' => ['old' => 'IN_CUSTODY', 'new' => 'DELIVERED']]),
                'reason' => ($isRezagado ? 'Entrega de rezago' : 'Entrega de resguardo') . ' por: ' . Auth::user()->name,
            ]);
        });

        $allowedRedirects = [
            route('gerencia.daily'),
            route('gerencia.rezagados.index'),
        ];
        $redirect = in_array($request->input('redirect_to'), $allowedRedirects, true)
            ? $request->input('redirect_to')
            : route('gerencia.daily');

        return redirect($redirect)->with('success', 'Resguardo entregado correctamente.');
    }

    /**
     * Búsqueda de clientes para vincular titular en entrega.
     */
    public function searchCustomers(Request $request)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $search = trim($request->get('q', ''));
        if ($search === '') {
            return response()->json([]);
        }

        if (!is_numeric($search) && strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Customer::select('id', 'name', 'customer_number', 'client_type');

        if (is_numeric($search) || preg_match('/^[A-Za-z0-9]+$/', $search)) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_number', 'LIKE', "{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%");
            });
        } else {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        return response()->json($query->limit(15)->get());
    }

    private function storeSignatureImage(string $signature): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $signature)) {
            return null;
        }

        $data = substr($signature, strpos($signature, ',') + 1);
        $data = base64_decode($data);
        if ($data === false) {
            return null;
        }

        $fileName = 'signatures/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $data);

        return $fileName;
    }

    /**
     * STORE: Crear nuevo resguardo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'ticket_date'  => 'required|date',
            'client_ref_id' => 'required|string|max:50',
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA',
            'pieces'       => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:500',
            'is_third_party' => 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        $validated['is_third_party'] = $request->has('is_third_party');

        if (!$validated['is_third_party']) {
            $validated['receiver_name'] = null;
        }

        Pickup::create($validated);

        return redirect()->route('gerencia.daily')
            ->with('success', 'Paquete registrado correctamente.');
    }

    /**
     * UPDATE: Editar con Auditoría.
     */
    public function update(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        if (!$pickup->created_at->isToday()) {
            return redirect()->route('gerencia.daily')->with('error', 'Los registros de días anteriores son de solo lectura.');
        }

        $validated = $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio,' . $id,
            'client_name'  => 'required|string|max:150',
            'department'   => 'required|in:AROMAS,BELLAROMA,CALLCENTER',
            'pieces'       => 'required|integer|min:1',
            'notes'        => 'nullable|string|max:500',
            'is_third_party' => 'nullable|boolean',
            'receiver_name' => 'nullable|string|max:150',
        ]);

        $validated['is_third_party'] = $request->has('is_third_party');

        $pickup->fill($validated);

        if ($pickup->isDirty()) {
            $changes = [];
            foreach ($pickup->getDirty() as $field => $newValue) {
                $changes[$field] = [
                    'old' => $pickup->getOriginal($field),
                    'new' => $newValue
                ];
            }

            DB::transaction(function () use ($pickup, $changes) {
                $pickup->save();
                PickupEdit::create([
                    'pickup_id' => $pickup->id,
                    'user_id' => Auth::id(),
                    'changes' => json_encode($changes),
                    'reason' => 'Edición manual desde Operación Diaria'
                ]);
            });

            return redirect()->route('gerencia.daily')->with('success', 'Resguardo actualizado y auditado.');
        }

        return redirect()->route('gerencia.daily')->with('info', 'No se detectaron cambios.');
    }

    public function destroy(Request $request, $id) // <-- AGREGAR Request $request
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        // Verificar contraseña del gerente
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()->route('gerencia.daily')->with('error', 'Contraseña incorrecta. Eliminación cancelada.');
        }

        $pickup = Pickup::findOrFail($id);

        // Bloqueo: No eliminar entregados
        if ($pickup->currentStatus?->code === 'DELIVERED') {
            return redirect()->route('gerencia.daily')->with('error', 'No se pueden eliminar resguardos ya entregados.');
        }

        // Auditoría de borrado
        DB::table('pickup_deleted_audits')->insert([
            'original_pickup_id' => $pickup->id,
            'ticket_folio' => $pickup->ticket_folio,
            'deleted_by' => Auth::id(),
            'pickup_data' => $pickup->toJson(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($pickup->initial_evidence_path) Storage::disk('public')->delete($pickup->initial_evidence_path);
        if ($pickup->package_evidence_path) Storage::disk('public')->delete($pickup->package_evidence_path);
        if ($pickup->evidence_path) Storage::disk('public')->delete($pickup->evidence_path);

        $pickup->delete();

        return redirect()->route('gerencia.daily')->with('success', 'Resguardo eliminado y registrado en auditoría.');
    }

    /**
     * FASE 1: Check-In Express por el Gerente (Mobile Optimized)
     */
    public function storePreliminar(Request $request)
    {
        $request->validate([
            'ticket_folio' => 'required|string|max:50|unique:pickups,ticket_folio',
            'department' => 'required|in:AROMAS,BELLAROMA,CALLCENTER',
            'initial_evidence' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $evidencePath = null;
        if ($request->hasFile('initial_evidence')) {
            $evidencePath = $request->file('initial_evidence')->store('pickups/initial_evidence', 'public');
        }

        $statusId = PickupStatus::where('code', 'PRE_REGISTERED')->value('id');

        Pickup::create([
            'ticket_folio' => $request->ticket_folio,
            'ticket_date' => now(),
            'department' => $request->department,
            'pieces' => 1, // Provisional, el checador pondrá el real
            'notes' => $request->notes,
            'initial_evidence_path' => $evidencePath,
            'is_complementary' => false, // El checador lo definirá
            'parent_pickup_id' => null,
            'status_id' => $statusId,
            'client_name' => 'Pendiente por Checador',
            'amount' => 0,
            'balance' => 0,
        ]);

        return redirect()->route('gerencia.daily')->with('success', 'Check-In rápido exitoso. El checador completará los detalles.');
    }

    /**
     * FASE 3: Aprobar resguardo (Pasa a Custodia oficial)
     */
    public function approveAudit($id)
    {
        $pickup = Pickup::findOrFail($id);
        $inCustodyId = \App\Models\PickupStatus::where('code', 'IN_CUSTODY')->value('id');

        $pickup->update([
            'status_id' => $inCustodyId,
            'correction_notes' => null
        ]);

        return redirect()->route('gerencia.daily')->with('success', 'Resguardo aprobado. Ya está en custodia oficial.');
    }

    /**
     * FASE 3: Rechazar resguardo (Regresa al checador)
     */
    public function rejectAudit(Request $request, $id)
    {
        $pickup = Pickup::findOrFail($id);

        $request->validate([
            'correction_notes' => 'required|string|max:500'
        ]);

        $needsCorrectionId = \App\Models\PickupStatus::where('code', 'NEEDS_CORRECTION')->value('id');

        $pickup->update([
            'status_id' => $needsCorrectionId,
            'correction_notes' => $request->correction_notes
        ]);

        return redirect()->route('gerencia.daily')->with('error', 'Resguardo devuelto al checador para corrección.');
    }

    /**
     * BUSCAR FOLIOS PARA RESGUARDOS COMPLEMENTARIOS
     */
    public function searchFolio(Request $request)
    {
        if ($request->ajax()) {
            $search = trim($request->get('q'));
            if (strlen($search) < 2) return response()->json([]);

            $pickups = Pickup::select('id', 'ticket_folio', 'client_name')
                ->where('ticket_folio', 'LIKE', "%{$search}%")
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json($pickups);
        }
        return response()->json(['error' => 'No autorizado'], 403);
    }

    /**
     * APROBACIÓN MASIVA DE RESGUARDOS
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'pickup_ids' => 'required|array',
            'pickup_ids.*' => 'exists:pickups,id'
        ]);

        $inCustodyId = \App\Models\PickupStatus::where('code', 'IN_CUSTODY')->value('id');

        Pickup::whereIn('id', $request->pickup_ids)
            ->whereHas('currentStatus', function ($q) {
                $q->where('code', 'PENDING_CONFIRMATION');
            })
            ->update([
                'status_id' => $inCustodyId,
                'correction_notes' => null
            ]);

        return redirect()->route('gerencia.daily')->with('success', 'Resguardos confirmados masivamente con éxito.');
    }

    /**
     * ELIMINACIÓN MASIVA CON SEGURIDAD
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'pickup_ids' => 'required|array',
            'pickup_ids.*' => 'exists:pickups,id',
            'password' => 'required|string'
        ]);

        // 1. Validar contraseña del gerente
        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()->route('gerencia.daily')->with('error', 'Contraseña incorrecta. Eliminación masiva cancelada.');
        }

        $pickups = Pickup::whereIn('id', $request->pickup_ids)->get();

        // 2. Verificar que NINGUNO esté entregado
        foreach ($pickups as $pickup) {
            if ($pickup->currentStatus?->code === 'DELIVERED') {
                return redirect()->route('gerencia.daily')->with('error', "El folio #{$pickup->ticket_folio} ya fue entregado y no puede borrarse. Se canceló toda la operación.");
            }
        }

        // 3. Proceso de borrado y auditoría
        DB::transaction(function () use ($pickups) {
            foreach ($pickups as $pickup) {
                DB::table('pickup_deleted_audits')->insert([
                    'original_pickup_id' => $pickup->id,
                    'ticket_folio' => $pickup->ticket_folio,
                    'deleted_by' => Auth::id(),
                    'pickup_data' => $pickup->toJson(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Limpieza de archivos
                if ($pickup->initial_evidence_path) Storage::disk('public')->delete($pickup->initial_evidence_path);
                if ($pickup->package_evidence_path) Storage::disk('public')->delete($pickup->package_evidence_path);
                if ($pickup->evidence_path) Storage::disk('public')->delete($pickup->evidence_path);
                if ($pickup->signature_path) Storage::disk('public')->delete($pickup->signature_path);

                $pickup->delete();
            }
        });

        return redirect()->route('gerencia.daily')->with('success', count($request->pickup_ids) . ' resguardos eliminados y auditados correctamente.');
    }
}
