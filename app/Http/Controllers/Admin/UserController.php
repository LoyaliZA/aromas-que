<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios con su información de empleado.
     */
    public function index()
    {
        // Eager Loading ('employee'): Traemos la relación en la misma consulta SQL
        // para evitar el problema N+1 (Consultas excesivas).
        $users = User::with('employee')->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Guarda el nuevo usuario y su perfil de empleado.
     */
    public function store(Request $request)
    {
        // 1. Validación Estricta
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in(['ADMIN', 'MANAGER', 'CHECKER', 'SELLER'])],
            
            // Datos del Empleado (Solo si no es Admin puro, aunque el SRS dice que todos tienen rol)
            'employee_code' => 'required|string|unique:employees,employee_code',
            'full_name' => 'required|string|max:100',
        ]);

        try {
            // 2. Transacción Atómica
            DB::transaction(function () use ($validated) {
                
                // A. Crear el Usuario (Login)
                $user = User::create([
                    'name' => $validated['name'], // Nombre de usuario (ej. 'vendedor1')
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'],
                    'is_active' => true,
                ]);

                // B. Crear el Perfil de Empleado (Negocio)
                Employee::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['full_name'], // Nombre real (ej. 'Juan Pérez')
                    'employee_code' => $validated['employee_code'],
                    'is_active' => true,
                ]);
            });

            // 3. Redirección Exitosa
            return redirect()->route('admin.users.index')
                ->with('success', 'Personal registrado correctamente.');

        } catch (\Exception $e) {
            // Si falla, volvemos al formulario con el error
            return back()->withInput()
                ->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(User $user)
    {
        // Cargamos los datos del empleado para rellenar el formulario
        $user->load('employee');
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Actualiza los datos.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['ADMIN', 'MANAGER', 'CHECKER', 'SELLER'])],
            'employee_code' => ['required', Rule::unique('employees')->ignore($user->employee->id ?? null)],
            'full_name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            // Actualizar Usuario
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'is_active' => $request->has('is_active'), // Checkbox handling
            ]);

            // Actualizar Password solo si se escribió algo
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Actualizar Empleado
            if ($user->employee) {
                $user->employee->update([
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'is_active' => $request->has('is_active'),
                ]);
            } else {
                // Caso raro: Si el usuario existía pero no tenía empleado (reparación)
                Employee::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['full_name'],
                    'employee_code' => $validated['employee_code'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Soft Delete: No borramos de la BD, solo desactivamos el acceso.
     */
    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            $user->update(['is_active' => false]);
            if ($user->employee) {
                $user->employee->update(['is_active' => false]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario desactivado (Baja Lógica).');
    }
}