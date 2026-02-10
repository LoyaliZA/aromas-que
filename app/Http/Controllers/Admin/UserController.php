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
        // Esto ya lo tienes configurado para tu tabla Dark Mode
        $employees = Employee::with('user')
            ->orderBy('is_active', 'desc')
            ->orderBy('job_position', 'asc')
            ->paginate(10);

        return view('admin.users.index', compact('employees'));
    }

    public function create()
    {
        // Retornamos la vista del formulario (que haremos en el paso 2)
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // 1. Validamos datos del EMPLEADO (Lo más importante)
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER',
            // Datos opcionales para login (Solo si llenan el email)
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|required_with:email|min:8',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                
                $userId = null;

                // 2. ¿El administrador llenó email y contraseña?
                // Entonces creamos un Usuario de Sistema (Login)
                if (!empty($validated['email']) && !empty($validated['password'])) {
                    $user = User::create([
                        'name' => $validated['full_name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'role' => $validated['job_position'], // El rol de usuario iguala al puesto
                        'is_active' => true,
                    ]);
                    $userId = $user->id;
                }

                // 3. Creamos SIEMPRE al Empleado (Negocio)
                Employee::create([
                    'user_id' => $userId, // Puede ser null si es solo vendedor
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
                    'is_active' => true,
                ]);
            });

            return redirect()->route('admin.users.index')
                ->with('success', 'Colaborador registrado correctamente.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario de edición.
     * Recibimos el ID del EMPLEADO (no del usuario).
     */
    public function edit($id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        return view('admin.users.edit', compact('employee'));
    }

    /**
     * Actualiza los datos del empleado y su acceso.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'employee_code' => 'required|string|unique:employees,employee_code,' . $employee->id,
            'job_position' => 'required|in:ADMIN,MANAGER,CHECKER,SELLER',
            // Email es requerido SOLO si el checkbox 'enable_login' viene marcado
            'email' => 'nullable|required_if:enable_login,on|email|unique:users,email,' . ($employee->user_id ?? 'null'),
            'password' => 'nullable|min:8',
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $employee) {
                
                // 1. Actualizar datos base del Empleado
                $employee->update([
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'job_position' => $validated['job_position'],
                ]);

                // 2. Gestión del Usuario (Login)
                if ($request->has('enable_login')) {
                    // CASO A: Quiere tener acceso web
                    
                    if ($employee->user) {
                        // Si ya tenía usuario, actualizamos
                        $userData = [
                            'name' => $validated['full_name'],
                            'email' => $validated['email'],
                            'role' => $validated['job_position'],
                        ];
                        // Solo cambiamos password si escribió una nueva
                        if (!empty($validated['password'])) {
                            $userData['password'] = Hash::make($validated['password']);
                        }
                        $employee->user->update($userData);
                    } else {
                        // Si no tenía, creamos uno nuevo y vinculamos
                        $user = User::create([
                            'name' => $validated['full_name'],
                            'email' => $validated['email'],
                            'password' => Hash::make($validated['password']), // Aquí sí es obligatoria
                            'role' => $validated['job_position'],
                            'is_active' => true,
                        ]);
                        $employee->update(['user_id' => $user->id]);
                    }

                } else {
                    // CASO B: No quiere acceso web (o se lo quitamos)
                    if ($employee->user) {
                        // Borramos el usuario para liberar el email y limpiar la BD
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

    /**
     * Desactivación Lógica (Soft Delete).
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Invertimos el estado (si está activo lo desactiva, y viceversa)
        $newState = !$employee->is_active;
        
        $employee->update(['is_active' => $newState]);

        // Si tiene usuario, también actualizamos su acceso
        if ($employee->user) {
            $employee->user->update(['is_active' => $newState]);
        }

        $status = $newState ? 'reactivado' : 'desactivado';
        return redirect()->route('admin.users.index')
            ->with('success', "Colaborador $status correctamente.");
    }
}