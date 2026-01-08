<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Paciente::with('tipoDocumento')->withInactive();

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado !== 'todos') {
            if ($request->estado === 'activos') {
                $query->active();
            } else {
                $query->inactive();
            }
        }

        $pacientes = $query->orderBy('created_at', 'desc')->get();

        return response()->json($pacientes);
    }

    /**
     * Get tipos de documento activos
     */
    public function getTiposDocumento()
    {
        $tipos = TipoDocumento::active()->orderBy('codigo')->get();
        return response()->json($tipos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p_nombres' => 'required|string|max:255',
            's_nombres' => 'nullable|string|max:255',
            'p_apellidos' => 'required|string|max:255',
            's_apellidos' => 'nullable|string|max:255',
            'tipo_documento_id' => 'required|exists:tipos_documento,id',
            'documento_numero' => 'required|string|max:255|unique:pacientes,documento_numero',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F,Otro',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'departamento' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            // Mensajes personalizados en español
            'p_nombres.required' => 'El primer nombre es obligatorio',
            'p_apellidos.required' => 'El primer apellido es obligatorio',
            'tipo_documento_id.required' => 'Debe seleccionar un tipo de documento',
            'tipo_documento_id.exists' => 'El tipo de documento seleccionado no es válido',
            'documento_numero.required' => 'El número de documento es obligatorio',
            'documento_numero.unique' => 'Ya existe un paciente registrado con este número de documento',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'sexo.required' => 'Debe seleccionar el sexo',
            'sexo.in' => 'El sexo seleccionado no es válido',
            'email.email' => 'El correo electrónico no tiene un formato válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Por favor corrija los errores en el formulario',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $paciente = Paciente::create($data);

        return response()->json($paciente->load('tipoDocumento'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $paciente = Paciente::withInactive()->with('tipoDocumento')->findOrFail($id);
        return response()->json($paciente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paciente = Paciente::withInactive()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'p_nombres' => 'required|string|max:255',
            's_nombres' => 'nullable|string|max:255',
            'p_apellidos' => 'required|string|max:255',
            's_apellidos' => 'nullable|string|max:255',
            'tipo_documento_id' => 'required|exists:tipos_documento,id',
            'documento_numero' => 'required|string|max:255|unique:pacientes,documento_numero,' . $id,
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F,Otro',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'departamento' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $paciente->update($data);

        return response()->json($paciente->fresh()->load('tipoDocumento'));
    }

    /**
     * Toggle status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        $paciente = Paciente::withInactive()->findOrFail($id);
        
        if ($paciente->is_active) {
            $paciente->deactivate();
        } else {
            $paciente->activate();
        }

        return response()->json($paciente->fresh()->load('tipoDocumento'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paciente = Paciente::withInactive()->findOrFail($id);
        $paciente->deactivate();
        return response()->json(['message' => 'Paciente desactivado correctamente']);
    }
}
