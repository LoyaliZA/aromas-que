<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')
            ->orderBy('is_active', 'desc')
            ->orderBy('job_position', 'asc')
            ->paginate(10);

        return view('admin.users.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER,AUXILIAR',
            'department' => 'required|in:AROMAS,BELLAROMA,CALLCENTER,CEDIS,NONE',
            'appears_in_sales_queue' => 'nullable|boolean',
            'can_manage_rezagados' => 'nullable|boolean', 
            'can_manage_shifts' => 'nullable|boolean',
            'has_access' => 'nullable|boolean',
            'email' => 'nullable|email|unique:users,email',
            'password' => $request->has('has_access') ? 'required|min:8' : 'nullable',
        ]);

        // Determinar el rol de acceso: Si es de logística, el rol es el departamento. Si no, es su puesto.
        $userRole = in_array($validated['department'], ['BELLAROMA', 'CALLCENTER', 'CEDIS']) 
            ? $validated['department'] 
            : $validated['job_position'];

        try {
            DB::transaction(function () use ($validated, $request, $userRole) {
                $userId = null;
                if ($request->has('has_access')) {
                    $user = User::create([
                        'name' => $validated['full_name'],
                        'email' => $validated['email'] ?? null,
                        'password' => Hash::make($validated['password']),
                        'role' => $userRole, 
                        'is_active' => true,
                        'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
                        'can_manage_shifts' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_shifts') : false,
                    ]);
                    $userId = $user->id;
                }

                Employee::create([
                    'user_id' => $userId,
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
                    'department' => $validated['department'],
                    'appears_in_sales_queue' => $request->has('appears_in_sales_queue'), 
                    'is_active' => true,
                    'hire_date' => now(),
                ]);
            });

            return redirect()->route('admin.users.index')
                ->with('success', 'Colaborador registrado exitosamente.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        return view('admin.users.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'employee_code' => ['required', Rule::unique('employees')->ignore($employee->id)],
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER,AUXILIAR',
            'department' => 'required|in:AROMAS,BELLAROMA,CALLCENTER,CEDIS,NONE',
            'appears_in_sales_queue' => 'nullable|boolean',
            'can_manage_rezagados' => 'nullable|boolean', 
            'can_manage_shifts' => 'nullable|boolean',
            'has_access' => 'nullable|boolean',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($employee->user_id)],
            'password' => ($request->has('has_access') && !$employee->user) ? 'required|min:8' : 'nullable|min:8', 
        ]);

        $userRole = in_array($validated['department'], ['BELLAROMA', 'CALLCENTER', 'CEDIS']) 
            ? $validated['department'] 
            : $validated['job_position'];

        try {
            DB::transaction(function () use ($validated, $request, $employee, $userRole) {
                
                $employee->update([
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
                    'department' => $validated['department'],
                    'appears_in_sales_queue' => $request->has('appears_in_sales_queue'),
                ]);

                if ($request->has('has_access')) {
                    if ($employee->user) {
                        $dataToUpdate = [
                            'name' => $validated['full_name'],
                            'email' => $validated['email'] ?? null,
                            'role' => $userRole,
                            'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
                            'can_manage_shifts' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_shifts') : false,
                        ];
                        if (!empty($validated['password'])) {
                            $dataToUpdate['password'] = Hash::make($validated['password']);
                        }
                        $employee->user->update($dataToUpdate);
                    } else {
                        $user = User::create([
                            'name' => $validated['full_name'],
                            'email' => $validated['email'] ?? null,
                            'password' => Hash::make($validated['password']),
                            'role' => $userRole,
                            'is_active' => true,
                            'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
                            'can_manage_shifts' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_shifts') : false,
                        ]);
                        $employee->update(['user_id' => $user->id]);
                    }

                } else {
                    if ($employee->user) {
                        $user = $employee->user;
                        $employee->update(['user_id' => null]);
                        $user->delete();
                    }
                }
            });

            return redirect()->route('admin.users.index')
                ->with('success', 'Información actualizada correctamente.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        
        $newState = !$employee->is_active;
        $employee->update(['is_active' => $newState]);

        if ($employee->user) {
            $employee->user->update(['is_active' => $newState]);
        }

        $status = $newState ? 'reactivado' : 'desactivado';
        return redirect()->route('admin.users.index')
            ->with('success', "Colaborador $status correctamente.");
    }
}