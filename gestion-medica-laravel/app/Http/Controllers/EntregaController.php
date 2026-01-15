<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EntregaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prescripcion_id' => 'required|exists:prescripciones,id',
            'fecha_programada' => 'required|date|after_or_equal:today',
            'cantidad_programada' => 'required|integer|min:1',
            'observaciones' => 'nullable|string'
        ]);

        $prescripcion = \App\Models\Prescripcion::with('entregas')->findOrFail($validated['prescripcion_id']);

        // 1. Validar que no haya entregas pendientes
        $pendientes = $prescripcion->entregas->whereIn('estado', ['PENDIENTE', 'PROGRAMADA', 'EN_RUTA'])->count();
        if ($pendientes > 0) {
            return response()->json([
                'message' => 'No puede agregar nuevas entregas mientras existan entregas pendientes o en ruta.'
            ], 422);
        }

        // 2. Validar Cantidad Restante
        $totalPrescrito = $prescripcion->cantidad_total;
        $entregadoReal = $prescripcion->entregas->where('entregado', true)->sum('cantidad_entregada_real');
        $saldoDisponible = $totalPrescrito - $entregadoReal;

        if ($saldoDisponible <= 0) {
            return response()->json([
                'message' => 'La prescripción ya ha sido entregada en su totalidad.'
            ], 422);
        }

        if ($validated['cantidad_programada'] > $saldoDisponible) {
            return response()->json([
                'message' => "La cantidad excede el saldo restante. Disponible: {$saldoDisponible}"
            ], 422);
        }

        // Calcular número de entrega consecutivo
        $nextNum = $prescripcion->entregas()->max('numero_entrega') + 1;

        $entrega = $prescripcion->entregas()->create([
            'numero_entrega' => $nextNum,
            'fecha_programada' => $validated['fecha_programada'],
            'cantidad_programada' => $validated['cantidad_programada'],
            'estado' => 'PENDIENTE',
            'entregado' => false,
            'observaciones' => $validated['observaciones'] ?? 'Entrega adicional'
        ]);
        
        if (!empty($validated['observaciones'])) {
            $entrega->observacionesHistorial()->create([
                'user_id' => Auth::id(),
                'observacion' => $validated['observaciones']
            ]);
        }

        return response()->json([
            'message' => 'Entrega agregada correctamente',
            'entrega' => $entrega->load(['validador', 'observacionesHistorial.user'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Entrega $entrega)
    {
        $validated = $request->validate([
            'entregado' => 'required|boolean',
            'cantidad_entregada_real' => 'required_if:entregado,true|integer|min:0',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:PENDIENTE,PROGRAMADA,EN_RUTA,ENTREGADO,NO_ENTREGADO,CANCELADA',
            'proxima_fecha_validacion' => 'nullable|date|after_or_equal:today'
        ]);

        $prescripcion = $entrega->prescripcion;

        // 1. Validar Vencimiento de Prescripción
        if ($prescripcion->fecha_vencimiento && Carbon::parse($prescripcion->fecha_vencimiento)->isPast()) {
            if ($prescripcion->estado !== 'VENCIDA') {
                $prescripcion->update(['estado' => 'VENCIDA']);
            }
            return response()->json(['message' => 'La prescripción está vencida. No se pueden procesar entregas.'], 422);
        }

        // 2. Validar Cantidad Total (No superar lo prescrito)
        if ($validated['entregado']) {
            $otrasEntregasSum = $prescripcion->entregas()
                ->where('entregado', true)
                ->where('id', '!=', $entrega->id)
                ->sum('cantidad_entregada_real');
            
            $nuevoTotal = $otrasEntregasSum + $validated['cantidad_entregada_real'];

            if ($nuevoTotal > $prescripcion->cantidad_total) {
                return response()->json([
                    'message' => "La cantidad excede el total prescrito. Entregado: {$otrasEntregasSum}, Nuevo: {$validated['cantidad_entregada_real']}, Total Permitido: {$prescripcion->cantidad_total}"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Guardar observación histórica si existe
            if (!empty($validated['observaciones'])) {
                $entrega->observacionesHistorial()->create([
                    'user_id' => Auth::id(),
                    'observacion' => $validated['observaciones']
                ]);
            }

            if ($validated['entregado']) {
                // Marcar como entregado
                $entrega->update([
                    'entregado' => true,
                    'cantidad_entregada_real' => $validated['cantidad_entregada_real'],
                    'fecha_entrega_real' => now(), // o fecha manual si se enviara
                    'user_id_validacion' => Auth::id(),
                    'estado' => $validated['estado'], // Permitir override o set EN_RUTA -> ENTREGADO
                    'observaciones' => $validated['observaciones'], // Mantener última obs visible
                    'proxima_fecha_validacion' => $validated['proxima_fecha_validacion']
                ]);
            } else {
                // Gestión de estados sin marcar entregado (ej: Programar siguiente llamada)
                $entrega->update([
                    'entregado' => false,
                    'cantidad_entregada_real' => null,
                    'fecha_entrega_real' => null,
                    'user_id_validacion' => null, // Reset validación si no está entregado
                    'estado' => $validated['estado'],
                    'observaciones' => $validated['observaciones'], // Mantener última obs visible
                    'proxima_fecha_validacion' => $validated['proxima_fecha_validacion']
                ]);
            }

            // Check si todas las entregas están completas para cerrar la prescripción
            /* 
            // Opcional: Cerrar prescripción si el total entregado >= total prescrito
             $totalEntregadoFinal = $otrasEntregasSum + ($validated['cantidad_entregada_real'] ?? 0);
             if ($totalEntregadoFinal >= $prescripcion->cantidad_total) {
                 $prescripcion->update(['estado' => 'COMPLETADA']);
             }
            */

            DB::commit();
            
            // Retornar entrega fresca con relaciones necesarias
            return response()->json([
                'message' => 'Entrega actualizada correctamente',
                'entrega' => $entrega->load(['validador', 'observacionesHistorial.user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar entrega: ' . $e->getMessage()], 500);
        }
    }
}
