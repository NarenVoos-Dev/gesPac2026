<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoDocumentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposDocumento = TipoDocumento::withInactive()->orderBy('codigo')->get();
        return response()->json($tiposDocumento);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:10|unique:tipos_documento,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $tipoDocumento = TipoDocumento::create($request->all());

        return response()->json($tipoDocumento, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoDocumento $tipoDocumento)
    {
        return response()->json($tipoDocumento);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:10|unique:tipos_documento,codigo,' . $tipoDocumento->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $tipoDocumento->update($request->all());

        return response()->json($tipoDocumento);
    }

    /**
     * Toggle status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        // Buscar sin el scope global para poder activar registros inactivos
        $tipoDocumento = TipoDocumento::withInactive()->findOrFail($id);
        
        if ($tipoDocumento->is_active) {
            $tipoDocumento->deactivate();
        } else {
            $tipoDocumento->activate();
        }

        return response()->json($tipoDocumento->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoDocumento $tipoDocumento)
    {
        // No eliminamos, solo desactivamos
        $tipoDocumento->deactivate();
        return response()->json(['message' => 'Tipo de documento desactivado correctamente']);
    }
}
