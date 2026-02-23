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
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER',
            'appears_in_sales_queue' => 'nullable|boolean',
            'can_manage_rezagados' => 'nullable|boolean', // <-- NUEVO CAMPO
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|required_with:email|min:8',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                // A. Crear Usuario (Si aplica)
                $userId = null;
                if (!empty($validated['email']) && !empty($validated['password'])) {
                    $user = User::create([
                        'name' => $validated['full_name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'role' => $validated['job_position'], 
                        'is_active' => true,
                        // Solo otorgamos el poder si es Manager y marcó la casilla
                        'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
                    ]);
                    $userId = $user->id;
                }

                // B. Crear Empleado
                Employee::create([
                    'user_id' => $userId,
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
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
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER',
            'appears_in_sales_queue' => 'nullable|boolean',
            'can_manage_rezagados' => 'nullable|boolean', // <-- NUEVO CAMPO
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($employee->user_id)],
            'password' => 'nullable|min:8', 
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $employee) {
                
                // 1. Actualizar Empleado
                $employee->update([
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
                    'appears_in_sales_queue' => $request->has('appears_in_sales_queue'),
                ]);

                // 2. Lógica de Usuario
                if (!empty($validated['email'])) {
                    if ($employee->user) {
                        $dataToUpdate = [
                            'name' => $validated['full_name'],
                            'email' => $validated['email'],
                            'role' => $validated['job_position'],
                            // Actualizamos el permiso de rezagados
                            'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
                        ];
                        if (!empty($validated['password'])) {
                            $dataToUpdate['password'] = Hash::make($validated['password']);
                        }
                        $employee->user->update($dataToUpdate);
                    } else {
                        $user = User::create([
                            'name' => $validated['full_name'],
                            'email' => $validated['email'],
                            'password' => Hash::make($validated['password'] ?? 'aromas123'),
                            'role' => $validated['job_position'],
                            'is_active' => true,
                            'can_manage_rezagados' => $validated['job_position'] === 'MANAGER' ? $request->has('can_manage_rezagados') : false,
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