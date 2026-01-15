<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->has('active_only') && $request->active_only == 'true') {
            $query->where('is_active', true);
        }

        $sort = $request->input('sort', 'nombre');
        $direction = $request->input('direction', 'asc');

        $productos = $query->orderBy($sort, $direction)->get();

        return response()->json($productos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:productos,codigo|max:50',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0', // Idealmente > costo, pero no bloqueante
            'is_active' => 'boolean'
        ], [
            'codigo.unique' => 'El código de producto ya existe en el sistema.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
            'costo.min' => 'El costo no puede ser negativo.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.'
        ]);

        $producto = Producto::create($validated);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $producto = Producto::findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('productos')->ignore($producto->id)],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ], [
            'codigo.unique' => 'El código de producto ya existe en el sistema.',
            'codigo.required' => 'El código es obligatorio.'
        ]);

        $producto->update($validated);

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete(); // Soft delete

        return response()->json([
            'message' => 'Producto eliminado correctamente'
        ]);
    }

    /**
     * Toggle status (Active/Inactive).
     */
    public function toggleStatus($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->is_active = !$producto->is_active;
        $producto->save();

        return response()->json([
            'message' => 'Estado del producto actualizado',
            'is_active' => $producto->is_active
        ]);
    }

    /**
     * Restore soft-deleted product.
     */
    public function restore($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);
        $producto->restore();

        return response()->json([
            'message' => 'Producto restaurado correctamente',
            'producto' => $producto
        ]);
    }
}
