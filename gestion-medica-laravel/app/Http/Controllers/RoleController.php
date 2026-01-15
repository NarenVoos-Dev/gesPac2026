<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions->count(),
                    'permissions' => $role->permissions->pluck('name'),
                ];
            });

        return response()->json($roles);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role,
        ], 201);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevenir edición de roles del sistema
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return response()->json([
                'message' => 'No se puede editar este rol del sistema',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role,
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevenir eliminación de roles del sistema
        if (in_array($role->name, ['super-admin', 'admin', 'agente', 'visitador', 'profesional'])) {
            return response()->json([
                'message' => 'No se puede eliminar este rol del sistema',
            ], 403);
        }

        // Verificar que no tenga usuarios asignados
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un rol que tiene usuarios asignados',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente',
        ]);
    }

    /**
     * Get permissions for a role.
     */
    public function permissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'role' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Sync permissions for a role.
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permisos actualizados exitosamente',
            'role' => $role->load('permissions'),
        ]);
    }
}
