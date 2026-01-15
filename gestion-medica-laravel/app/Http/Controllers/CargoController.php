<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cargo::query();

        // Filtro por estado
        if ($request->has('estado')) {
            if ($request->estado === 'activos') {
                $query->where('is_active', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('is_active', false);
            }
        } else {
            // Por defecto solo activos
            $query->where('is_active', true);
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $cargos = $query->orderBy('nombre')->get();

        return response()->json($cargos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:cargos,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique' => 'Ya existe un cargo con este código',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        $cargo = Cargo::create($validated);

        return response()->json([
            'message' => 'Cargo creado exitosamente',
            'cargo' => $cargo,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cargo $cargo)
    {
        return response()->json($cargo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cargo $cargo)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:cargos,codigo,' . $cargo->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique' => 'Ya existe un cargo con este código',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        $cargo->update($validated);

        return response()->json([
            'message' => 'Cargo actualizado exitosamente',
            'cargo' => $cargo,
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Cargo $cargo)
    {
        $cargo->update(['is_active' => false]);

        return response()->json([
            'message' => 'Cargo desactivado exitosamente',
        ]);
    }

    /**
     * Restore a soft deleted resource.
     */
    public function restore($id)
    {
        $cargo = Cargo::findOrFail($id);
        $cargo->update(['is_active' => true]);

        return response()->json([
            'message' => 'Cargo activado exitosamente',
            'cargo' => $cargo,
        ]);
    }

    /**
     * Get system labels for core roles
     */
    public function getEtiquetasSistema()
    {
        $codigos = ['AGENTE', 'VISITADOR_MEDICO', 'PROFESIONAL_SALUD'];
        
        $cargos = Cargo::whereIn('codigo', $codigos)->get();
        // Create lookup array
        $map = [];
        foreach ($cargos as $cargo) {
            $map[$cargo->codigo] = $cargo->nombre;
        }
            
        return response()->json([
            'AGENTE' => $map['AGENTE'] ?? 'Agente',
            'VISITADOR_MEDICO' => $map['VISITADOR_MEDICO'] ?? 'Visitador Médico',
            'PROFESIONAL_SALUD' => $map['PROFESIONAL_SALUD'] ?? 'Profesional de la Salud',
        ]);
    }
}
