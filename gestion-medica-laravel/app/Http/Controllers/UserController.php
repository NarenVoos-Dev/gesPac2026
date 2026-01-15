<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with(['empleado', 'roles']);

        // Filtro por rol
        if ($request->has('rol') && $request->rol) {
            $query->role($request->rol);
        }

        // Filtro por estado
        if ($request->has('estado')) {
            if ($request->estado === 'activos') {
                $query->where('is_active', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('is_active', false);
            }
        } else {
            // Por defecto, solo activos
            $query->where('is_active', true);
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->orderBy('name')->get();

        return response()->json($usuarios);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $usuario = User::with(['empleado.cargoPrincipal', 'roles', 'permissions'])
            ->findOrFail($id);

        return response()->json($usuario);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'rol' => 'required|string|in:admin,agente,visitador,profesional',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // Asignar rol
        $user->assignRole($validated['rol']);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $user->load(['roles']),
        ], 201);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($usuario->id),
            ],
        ]);

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario->load(['empleado', 'roles']),
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'rol' => 'required|string|in:admin,agente,visitador,profesional',
        ]);

        // Remover roles actuales y asignar el nuevo
        $usuario->syncRoles([$validated['rol']]);

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'usuario' => $usuario->load(['empleado', 'roles']),
        ]);
    }

    /**
     * Toggle user status (activate/deactivate).
     */
    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        // Prevenir auto-desactivación
        if ($usuario->id === auth()->id()) {
            return response()->json([
                'message' => 'No puedes desactivar tu propio usuario',
            ], 403);
        }

        $usuario->is_active = !$usuario->is_active;
        $usuario->save();

        $action = $usuario->is_active ? 'activado' : 'desactivado';

        return response()->json([
            'message' => "Usuario {$action} exitosamente",
            'usuario' => $usuario->load(['empleado', 'roles']),
        ]);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $usuario->password = Hash::make($validated['password']);
        $usuario->save();

        return response()->json([
            'message' => 'Contraseña cambiada exitosamente',
        ]);
    }
}
