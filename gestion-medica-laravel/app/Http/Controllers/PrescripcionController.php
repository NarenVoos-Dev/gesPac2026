<?php

namespace App\Http\Controllers;

use App\Models\Prescripcion;
use App\Models\Entrega;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrescripcionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescripcion::with(['paciente', 'empleado', 'producto', 'entregas.validador', 'entregas.observacionesHistorial.user']); // Eager load historial

        if ($request->has('next_validation_date') && $request->next_validation_date) {
            $date = $request->next_validation_date;
            $query->whereHas('entregas', function($q) use ($date) {
                $q->whereDate('proxima_fecha_validacion', $date);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('paciente', function($qP) use ($search) {
                      $qP->where('documento_numero', 'like', "%{$search}%") // Buscar por documento
                         ->orWhere('nombres', 'like', "%{$search}%")       // Buscar por nombre
                         ->orWhere('apellidos', 'like', "%{$search}%");    // Buscar por apellido
                  })
                  // Búsqueda por entregas (fecha o estado)
                  ->orWhereHas('entregas', function($qE) use ($search) {
                      $qE->where('fecha_programada', 'like', "%{$search}%")
                         ->orWhere('estado', 'like', "%{$search}%");
                  });
            });
        }
        
        // Orden default
        $query->orderBy('created_at', 'desc');

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'numero' => 'required|string|unique:prescripciones,numero', // Validacion Manual
            'paciente_id' => 'required|exists:pacientes,id',
            'empleado_id' => 'required|exists:empleados,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_total' => 'required|integer|min:1',
            'numero_entregas' => 'required|integer|min:1',
            'fecha_prescripcion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_prescripcion',
            'ciudad' => 'required|string',
            'municipio' => 'nullable|string',
            'barrio' => 'nullable|string',
            'direccion' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            // Asignación de número manual
            $numero = $validated['numero'];
            
            $cantidadPorEntrega = intval($validated['cantidad_total'] / $validated['numero_entregas']);

            $prescripcion = Prescripcion::create([
                'numero' => $numero,
                'paciente_id' => $validated['paciente_id'],
                'empleado_id' => $validated['empleado_id'],
                'producto_id' => $validated['producto_id'],
                'cantidad_total' => $validated['cantidad_total'],
                'numero_entregas' => $validated['numero_entregas'],
                'cantidad_por_entrega' => $cantidadPorEntrega,
                'fecha_prescripcion' => $validated['fecha_prescripcion'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'ciudad' => $validated['ciudad'],
                'municipio' => $validated['municipio'],
                'barrio' => $validated['barrio'],
                'direccion' => $validated['direccion'],
                'diagnostico' => $request->diagnostico,
                'indicaciones' => $request->indicaciones,
                'observaciones' => $request->observaciones,
                'estado' => 'ACTIVA',
                'created_by' => auth()->id() ?? 1 // Fallback dev
            ]);

            // Generar Entregas
            $fechaBase = Carbon::parse($validated['fecha_prescripcion']);
            
            for ($i = 1; $i <= $validated['numero_entregas']; $i++) {
                // Frecuencia mensual por defecto
                // Entrega 1: Fecha prescripción? O +1 mes? 
                // Asumimos Entrega 1 = Fecha Prescripción (Inmediata/Primera dosis)
                // Entrega 2 = +1 mes, etc.
                // O si el usuario quiere diferente, debió especificarlo.
                // Modelo estándar: Despacho parcial periódico.
                
                $fechaProg = $i === 1 ? $fechaBase->copy() : $fechaBase->copy()->addMonths($i - 1);

                Entrega::create([
                    'prescripcion_id' => $prescripcion->id,
                    'numero_entrega' => $i,
                    'cantidad_programada' => $cantidadPorEntrega, // Podría haber residuo en la última
                    'fecha_programada' => $fechaProg,
                    'estado' => 'PENDIENTE'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Prescripción creada con éxito',
                'data' => $prescripcion->load('entregas')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear prescripción: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $prescripcion = Prescripcion::with(['paciente', 'empleado', 'producto', 'entregas.observaciones'])->findOrFail($id);
        return response()->json($prescripcion);
    }
    
    public function update(Request $request, $id)
    {
        // MVP: Solo permitir editar campos informativos, no estructura de entregas
        // Si quieren editar entregas, se debe hacer desde módulo Entregas o anular y recrear.
        $prescripcion = Prescripcion::findOrFail($id);
        
        $prescripcion->update($request->only([
            'ciudad', 'municipio', 'barrio', 'direccion', 
            'diagnostico', 'indicaciones', 'observaciones'
        ]));
        
        return response()->json(['message' => 'Actualizado', 'data' => $prescripcion]);
    }

    public function destroy($id)
    {
        $prescripcion = Prescripcion::findOrFail($id);
        
        // Soft delete y cancelar entregas pendientes
        DB::transaction(function() use ($prescripcion) {
            $prescripcion->estado = 'ANULADA';
            $prescripcion->save();
            $prescripcion->entregas()->where('estado', 'PENDIENTE')->update(['estado' => 'CANCELADA']);
            $prescripcion->delete();
        });
        
        return response()->json(['message' => 'Prescripción anulada']);
    }
}
