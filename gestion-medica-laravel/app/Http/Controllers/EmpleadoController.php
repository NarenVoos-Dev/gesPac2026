<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Cargo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Empleado::with(['user.roles', 'especialidades', 'cargo', 'cargos']);

        // Filtro por estado
        if ($request->has('estado')) {
            if ($request->estado === 'activos') {
                $query->where('is_active', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('is_active', false);
            }
        } else {
            $query->where('is_active', true);
        }

        // Filtro por cargo (ID)
        if ($request->has('cargo_id') && $request->cargo_id) {
            $query->whereHas('cargos', function ($q) use ($request) {
                $q->where('cargos.id', $request->cargo_id)
                  ->whereNull('empleado_cargo.fecha_fin');
            });
        }

        // Filtro por cargo (Código) - NUEVO
        if ($request->has('cargo_codigo') && $request->cargo_codigo) {
            $query->whereHas('cargos', function ($q) use ($request) {
                $q->where('cargos.codigo', $request->cargo_codigo)
                  ->whereNull('empleado_cargo.fecha_fin');
            });
        }

        // Include Visitadores - NUEVO
        if ($request->has('include_visitadores') && filter_var($request->include_visitadores, FILTER_VALIDATE_BOOLEAN)) {
            $query->with('visitadoresAsignados');
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('documento_numero', 'like', "%{$search}%")
                  ->orWhere('email_corporativo', 'like', "%{$search}%");
            });
        }

        $empleados = $query->orderBy('nombres')->get();
        
        // Cargar cargo principal después del get para evitar ambigüedad
        $empleados->load('cargoPrincipal');

        return response()->json($empleados);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'tipo_documento_id' => 'nullable|exists:tipos_documento,id',
            'documento_numero' => 'required|string|unique:empleados,documento_numero',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F,Otro',
            'telefono' => 'nullable|string',
            'email_personal' => 'nullable|email',
            'email_corporativo' => 'nullable|email|unique:empleados,email_corporativo',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string',
            'departamento' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'cargo_id' => 'required|exists:cargos,id',
            'crear_usuario' => 'boolean',
            'rol' => 'required_if:crear_usuario,true|nullable|string|in:admin,agente,visitador,profesional',
        ], [
            'nombres.required' => 'El nombre es obligatorio',
            'apellidos.required' => 'Los apellidos son obligatorios',
            'documento_numero.required' => 'El número de documento es obligatorio',
            'documento_numero.unique' => 'Ya existe un empleado con este documento',
            'email_corporativo.unique' => 'Ya existe un empleado con este email corporativo',
            'cargo_id.required' => 'El cargo es obligatorio',
            'rol.required_if' => 'Debe seleccionar un rol cuando crea un usuario',
            'rol.in' => 'El rol seleccionado no es válido',
        ]);

        DB::beginTransaction();
        try {
            // Crear empleado
            $empleado = Empleado::create($validated);

            // Asignar cargo principal
            $empleado->asignarCargo($validated['cargo_id'], true);

            // Crear usuario si se solicitó
            if ($request->crear_usuario && $request->email_corporativo) {
                $user = User::create([
                    'empleado_id' => $empleado->id,
                    'name' => $empleado->nombre_completo,
                    'email' => $request->email_corporativo,
                    'password' => Hash::make('password123'), // Cambiar en producción
                    'is_active' => true,
                ]);

                // Asignar rol
                if ($request->rol) {
                    $user->assignRole($request->rol);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Empleado creado exitosamente',
                'empleado' => $empleado->load(['cargoPrincipal', 'user.roles']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el empleado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        return response()->json(
            $empleado->load([
                'cargos',
                'cargoPrincipal',
                'especialidades',
                'user.roles',
                'agentes',
                'visitadores',
                'profesionales',
                'visitadoresAsignados'
            ])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'tipo_documento_id' => 'nullable|exists:tipos_documento,id',
            'documento_numero' => 'required|string|unique:empleados,documento_numero,' . $empleado->id,
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F,Otro',
            'telefono' => 'nullable|string',
            'email_personal' => 'nullable|email',
            'email_corporativo' => 'nullable|email|unique:empleados,email_corporativo,' . $empleado->id,
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string',
            'departamento' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'cargos' => 'nullable|array',
            'cargos.*' => 'exists:cargos,id',
        ]);

        $empleado->update($validated);

        // Sincronizar cargos si se enviaron
        if (isset($validated['cargos']) && !empty($validated['cargos'])) {
            // Preparar datos para sync con pivot
            $cargosSync = [];
            foreach ($validated['cargos'] as $index => $cargoId) {
                $cargosSync[$cargoId] = [
                    'es_principal' => $index === 0 ? 1 : 0, // El primero es principal
                    'fecha_asignacion' => now()->format('Y-m-d'),
                ];
            }
            $empleado->cargos()->sync($cargosSync);
        }

        return response()->json([
            'message' => 'Empleado actualizado exitosamente',
            'empleado' => $empleado->load(['cargos', 'user.roles']),
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->deactivate();

        return response()->json([
            'message' => 'Empleado desactivado exitosamente',
        ]);
    }

    /**
     * Restore a soft deleted resource.
     */
    public function restore($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->activate();

        return response()->json([
            'message' => 'Empleado activado exitosamente',
            'empleado' => $empleado,
        ]);
    }

    /**
     * Asignar cargo a empleado
     */
    public function asignarCargo(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        
        $validated = $request->validate([
            'cargo_id' => 'required|exists:cargos,id',
            'es_principal' => 'boolean',
        ]);

        $empleado->asignarCargo(
            $validated['cargo_id'],
            $validated['es_principal'] ?? false
        );

        return response()->json([
            'message' => 'Cargo asignado exitosamente',
            'empleado' => $empleado->load('cargos'),
        ]);
    }

    /**
     * Asignar especialidades a empleado (profesional)
     */
    public function asignarEspecialidades(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        
        $validated = $request->validate([
            'especialidades' => 'required|array',
            'especialidades.*' => 'exists:especialidades,id',
        ]);

        $empleado->especialidades()->sync($validated['especialidades']);

        return response()->json([
            'message' => 'Especialidades asignadas exitosamente',
            'empleado' => $empleado->load('especialidades'),
        ]);
    }

    /**
     * Asignar agentes a visitador
     */
    public function asignarAgentes(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        
        $validated = $request->validate([
            'agentes' => 'required|array',
            'agentes.*' => 'exists:empleados,id',
        ]);

        $empleado->agentes()->sync($validated['agentes']);

        return response()->json([
            'message' => 'Agentes asignados exitosamente',
            'empleado' => $empleado->load('agentes'),
        ]);
    }

    /**
     * Asignar profesionales a visitador
     */
    public function asignarProfesionales(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        
        $validated = $request->validate([
            'profesionales' => 'required|array',
            'profesionales.*' => 'exists:empleados,id',
        ]);

        $empleado->profesionales()->sync($validated['profesionales']);

        return response()->json([
            'message' => 'Profesionales asignados exitosamente',
            'empleado' => $empleado->load('profesionales'),
        ]);
    }

    /**
     * Obtener visitadores de un agente
     */
    public function obtenerVisitadores($id)
    {
        $empleado = Empleado::findOrFail($id);
        return response()->json($empleado->visitadores);
    }

    /**
     * Obtener profesionales de un visitador
     */
    public function obtenerProfesionales($id)
    {
        $empleado = Empleado::findOrFail($id);
        return response()->json($empleado->profesionales);
    }

    /**
     * Obtener todos los cargos para el selector
     */
    public function getCargos()
    {
        $cargos = Cargo::active()->orderBy('nombre')->get();
        return response()->json($cargos);
    }

    /**
     * Create user for existing employee.
     */
    public function crearUsuario(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);

        // Verificar que no tenga usuario
        if ($empleado->user) {
            return response()->json([
                'message' => 'Este empleado ya tiene un usuario asignado',
            ], 422);
        }

        // Verificar que tenga email
        $email = $empleado->email_corporativo ?? $empleado->email_personal;
        if (!$email) {
            return response()->json([
                'message' => 'El empleado debe tener un email (corporativo o personal) para crear un usuario',
            ], 422);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8',
            'rol' => 'required|string|in:admin,agente,visitador,profesional',
        ]);

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'name' => $empleado->nombres . ' ' . $empleado->apellidos,
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'is_active' => $empleado->is_active,
                'empleado_id' => $empleado->id, // Vincular con empleado
            ]);

            // Asignar rol
            $user->assignRole($validated['rol']);

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado y vinculado exitosamente',
                'user' => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
            ], 500);
        }
    }
}
