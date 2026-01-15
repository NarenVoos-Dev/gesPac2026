<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::with('roles')
            ->get()
            ->map(function ($permission) {
                // Extraer módulo del nombre (ej: "pacientes.ver" -> "pacientes")
                $parts = explode('.', $permission->name);
                $module = $parts[0] ?? 'general';

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $module,
                    'guard_name' => $permission->guard_name,
                    'roles' => $permission->roles->pluck('name'),
                ];
            })
            ->groupBy('module');

        return response()->json($permissions);
    }

    /**
     * Store a new permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return response()->json([
            'message' => 'Permiso creado exitosamente',
            'permission' => $permission,
        ], 201);
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update($validated);

        return response()->json([
            'message' => 'Permiso actualizado exitosamente',
            'permission' => $permission,
        ]);
    }

    /**
     * Remove the specified permission.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Verificar que no esté asignado a ningún rol
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un permiso que está asignado a roles',
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permiso eliminado exitosamente',
        ]);
    }
}
