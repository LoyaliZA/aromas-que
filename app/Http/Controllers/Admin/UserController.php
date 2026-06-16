<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\CatalogResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user.catalogRole', 'catalogJobPosition', 'catalogDepartment'])
            ->orderBy('is_active', 'desc')
            ->orderBy('full_name', 'asc')
            ->paginate(10);

        return view('admin.users.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.users.create', $this->catalogFormData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);

        $userRole = CatalogResolver::resolveAccessRole(
            $validated['department'],
            $validated['job_position']
        );

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
        $employee = Employee::with(['user', 'catalogJobPosition', 'catalogDepartment'])->findOrFail($id);

        return view('admin.users.edit', array_merge(
            ['employee' => $employee],
            $this->catalogFormData()
        ));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        $validated = $this->validateEmployee($request, $employee);

        $userRole = CatalogResolver::resolveAccessRole(
            $validated['department'],
            $validated['job_position']
        );

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

    private function catalogFormData(): array
    {
        $jobPositionLabels = config('catalog_labels.job_positions', []);
        $departmentLabels = config('catalog_labels.departments', []);

        return [
            'jobPositions' => JobPosition::active()->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'jobPositionLabels' => $jobPositionLabels,
            'departmentLabels' => $departmentLabels,
        ];
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $jobPositionRule = Rule::exists('job_positions', 'name')->where('is_active', true);
        $departmentRule = Rule::exists('departments', 'name')->where('is_active', true);

        return $request->validate([
            'full_name' => 'required|string|max:100',
            'employee_code' => [
                'required',
                Rule::unique('employees')->ignore($employee?->id),
            ],
            'job_position' => ['required', 'string', $jobPositionRule],
            'department' => ['required', 'string', $departmentRule],
            'appears_in_sales_queue' => 'nullable|boolean',
            'can_manage_rezagados' => 'nullable|boolean',
            'can_manage_shifts' => 'nullable|boolean',
            'has_access' => 'nullable|boolean',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($employee?->user_id),
            ],
            'password' => ($request->has('has_access') && !$employee?->user)
                ? 'required|min:8'
                : 'nullable|min:8',
        ]);
    }
}
