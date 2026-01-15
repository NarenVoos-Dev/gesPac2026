<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Especialidad::query();

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

        $especialidades = $query->orderBy('nombre')->get();

        return response()->json($especialidades);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:especialidades,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique' => 'Ya existe una especialidad con este código',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        $especialidad = Especialidad::create($validated);

        return response()->json([
            'message' => 'Especialidad creada exitosamente',
            'especialidad' => $especialidad,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Especialidad $especialidad)
    {
        return response()->json($especialidad);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Especialidad $especialidad)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:especialidades,codigo,' . $especialidad->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ], [
            'codigo.required' => 'El código es obligatorio',
            'codigo.unique' => 'Ya existe una especialidad con este código',
            'nombre.required' => 'El nombre es obligatorio',
        ]);

        $especialidad->update($validated);

        return response()->json([
            'message' => 'Especialidad actualizada exitosamente',
            'especialidad' => $especialidad,
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Especialidad $especialidad)
    {
        $especialidad->update(['is_active' => false]);

        return response()->json([
            'message' => 'Especialidad desactivada exitosamente',
        ]);
    }

    /**
     * Restore a soft deleted resource.
     */
    public function restore($id)
    {
        $especialidad = Especialidad::findOrFail($id);
        $especialidad->update(['is_active' => true]);

        return response()->json([
            'message' => 'Especialidad activada exitosamente',
            'especialidad' => $especialidad,
        ]);
    }
}
