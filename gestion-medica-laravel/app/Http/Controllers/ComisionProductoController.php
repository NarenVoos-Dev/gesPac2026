<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComisionProductoController extends Controller
{
    public function index(Request $request)
    {
        // Cargar cargoPrincipal (Pivot)
        $query = \App\Models\ComisionProducto::with(['empleado.cargo', 'producto:id,nombre,codigo']);

        // Filtro por Producto
        if ($request->has('producto_id') && $request->producto_id) {
            $query->where('producto_id', $request->producto_id);
        }

        // Filtro por Cargo (Relación Pivot Empleado -> Cargos)
        if ($request->has('cargo_id') && $request->cargo_id) {
            $query->whereHas('empleado', function ($q) use ($request) {
                // Filtrar sobre la relación cargoPrincipal o cargos
                $q->whereHas('cargoPrincipal', function ($qC) use ($request) {
                    $qC->where('cargos.id', $request->cargo_id);
                });
            });
        }

        // Buscador (Nombre empleado o Código/Nombre Producto)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('empleado', function ($qE) use ($search) {
                    $qE->where('nombres', 'like', "%{$search}%")
                       ->orWhere('apellidos', 'like', "%{$search}%")
                       ->orWhere('documento_numero', 'like', "%{$search}%");
                })->orWhereHas('producto', function ($qP) use ($search) {
                    $qP->where('nombre', 'like', "%{$search}%")
                       ->orWhere('codigo', 'like', "%{$search}%");
                });
            });
        }

        $comisiones = $query->get();
        return response()->json($comisiones);
    }

    public function store(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'producto_id' => 'required|exists:productos,id',
            'tipo_calculo' => 'required|in:PORCENTAJE,FIJO',
            'valor' => 'required|numeric|min:0',
        ]);

        // Validar duplicados manual (o rule unique)
        $existe = \App\Models\ComisionProducto::where('empleado_id', $request->empleado_id)
            ->where('producto_id', $request->producto_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'El empleado ya tiene asignada una comisión para este producto.',
                'errors' => ['producto_id' => ['Regla duplicada']]
            ], 422);
        }

        $comision = \App\Models\ComisionProducto::create($request->all());
        return response()->json($comision, 201);
    }

    public function update(Request $request, $id)
    {
        $comision = \App\Models\ComisionProducto::findOrFail($id);
        
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'producto_id' => 'required|exists:productos,id',
            'tipo_calculo' => 'required|in:PORCENTAJE,FIJO',
            'valor' => 'required|numeric|min:0',
        ]);

        // Validar duplicados excluyendo actual
        $existe = \App\Models\ComisionProducto::where('empleado_id', $request->empleado_id)
            ->where('producto_id', $request->producto_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
             return response()->json([
                'message' => 'El empleado ya tiene asignada una comisión para este producto.',
                'errors' => ['producto_id' => ['Regla duplicada']]
            ], 422);
        }

        $comision->update($request->all());
        return response()->json($comision);
    }

    public function destroy($id)
    {
        $comision = \App\Models\ComisionProducto::findOrFail($id);
        $comision->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
