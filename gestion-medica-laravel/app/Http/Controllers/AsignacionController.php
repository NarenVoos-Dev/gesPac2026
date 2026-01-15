<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Cargo;

class AsignacionController extends Controller
{
    /**
     * Get all agentes with their assigned visitadores
     */
    public function getAgentes()
    {
        $cargoAgente = Cargo::where('codigo', 'AGENTE')->first();
        
        if (!$cargoAgente) {
            return response()->json([]);
        }

        $agentes = Empleado::whereHas('cargos', function ($query) use ($cargoAgente) {
                $query->where('cargo_id', $cargoAgente->id);
            })
            ->where('is_active', true)
            ->with(['visitadores' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($agente) {
                return [
                    'id' => $agente->id,
                    'nombres' => $agente->nombres,
                    'apellidos' => $agente->apellidos,
                    'nombre_completo' => $agente->nombres . ' ' . $agente->apellidos,
                    'documento_numero' => $agente->documento_numero,
                    'visitadores_count' => $agente->visitadores->count(),
                    'visitadores' => $agente->visitadores->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'nombre_completo' => $v->nombres . ' ' . $v->apellidos,
                        ];
                    }),
                ];
            });

        return response()->json($agentes);
    }

    /**
     * Get all visitadores available for assignment
     */
    public function getVisitadoresDisponibles()
    {
        $cargoVisitador = Cargo::where('codigo', 'VISITADOR_MEDICO')->first();
        
        if (!$cargoVisitador) {
            return response()->json([]);
        }

        $visitadores = Empleado::whereHas('cargos', function ($query) use ($cargoVisitador) {
                $query->where('cargo_id', $cargoVisitador->id);
            })
            ->where('is_active', true)
            ->get()
            ->map(function ($visitador) {
                return [
                    'id' => $visitador->id,
                    'nombre_completo' => $visitador->nombres . ' ' . $visitador->apellidos,
                    'documento_numero' => $visitador->documento_numero,
                ];
            });

        return response()->json($visitadores);
    }

    /**
     * Assign visitadores to an agente
     */
    public function asignarVisitadores(Request $request, $agenteId)
    {
        $agente = Empleado::findOrFail($agenteId);

        $validated = $request->validate([
            'visitadores' => 'required|array',
            'visitadores.*' => 'exists:empleados,id',
        ]);

        // Sync visitadores (reemplaza asignaciones existentes)
        $agente->visitadores()->sync($validated['visitadores']);

        return response()->json([
            'message' => 'Visitadores asignados exitosamente',
            'visitadores_count' => count($validated['visitadores']),
        ]);
    }

    /**
     * Get visitadores assigned to an agente
     */
    public function getVisitadoresAsignados($agenteId)
    {
        $agente = Empleado::with('visitadores')->findOrFail($agenteId);

        $visitadores = $agente->visitadores->map(function ($v) {
            return [
                'id' => $v->id,
                'nombre_completo' => $v->nombres . ' ' . $v->apellidos,
            ];
        });

        return response()->json($visitadores);
    }

    /**
     * Get all visitadores with their assigned profesionales
     */
    public function getVisitadores()
    {
        $cargoVisitador = Cargo::where('codigo', 'VISITADOR_MEDICO')->first();
        
        if (!$cargoVisitador) {
            return response()->json([]);
        }

        $visitadores = Empleado::whereHas('cargos', function ($query) use ($cargoVisitador) {
                $query->where('cargo_id', $cargoVisitador->id);
            })
            ->where('is_active', true)
            ->with(['profesionales' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($visitador) {
                return [
                    'id' => $visitador->id,
                    'nombres' => $visitador->nombres,
                    'apellidos' => $visitador->apellidos,
                    'nombre_completo' => $visitador->nombres . ' ' . $visitador->apellidos,
                    'documento_numero' => $visitador->documento_numero,
                    'profesionales_count' => $visitador->profesionales->count(),
                    'profesionales' => $visitador->profesionales->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'nombre_completo' => $p->nombres . ' ' . $p->apellidos,
                        ];
                    }),
                ];
            });

        return response()->json($visitadores);
    }

    /**
     * Get all profesionales available for assignment
     */
    public function getProfesionalesDisponibles()
    {
        $cargoProfesional = Cargo::where('codigo', 'PROFESIONAL_SALUD')->first();
        
        if (!$cargoProfesional) {
            return response()->json([]);
        }

        $profesionales = Empleado::whereHas('cargos', function ($query) use ($cargoProfesional) {
                $query->where('cargo_id', $cargoProfesional->id);
            })
            ->where('is_active', true)
            ->get()
            ->map(function ($profesional) {
                return [
                    'id' => $profesional->id,
                    'nombre_completo' => $profesional->nombres . ' ' . $profesional->apellidos,
                    'documento_numero' => $profesional->documento_numero,
                ];
            });

        return response()->json($profesionales);
    }

    /**
     * Assign profesionales to a visitador
     */
    public function asignarProfesionales(Request $request, $visitadorId)
    {
        $visitador = Empleado::findOrFail($visitadorId);

        $validated = $request->validate([
            'profesionales' => 'required|array',
            'profesionales.*' => 'exists:empleados,id',
        ]);

        // Sync profesionales (reemplaza asignaciones existentes)
        $visitador->profesionales()->sync($validated['profesionales']);

        return response()->json([
            'message' => 'Profesionales asignados exitosamente',
            'profesionales_count' => count($validated['profesionales']),
        ]);
    }

    /**
     * Get profesionales assigned to a visitador
     */
    public function getProfesionalesAsignados($visitadorId)
    {
        $visitador = Empleado::with('profesionales')->findOrFail($visitadorId);

        $profesionales = $visitador->profesionales->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombres . ' ' . $p->apellidos,
            ];
        });

        return response()->json($profesionales);
    }
}
