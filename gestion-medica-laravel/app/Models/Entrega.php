<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entrega extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prescripcion_id',
        'paciente_id', // Added
        'numero_entrega',
        'cantidad_programada',
        'fecha_programada',
        'fecha_entrega_real',
        'cantidad_entregada_real', // Added
        'proxima_fecha_validacion',
        'estado',
        'entregado', // Added
        'user_id_validacion', // Added
        'observaciones',
    ];

    protected $casts = [
        'fecha_programada' => 'date:Y-m-d', // Modified
        'fecha_entrega_real' => 'date:Y-m-d', // Modified
        'proxima_fecha_validacion' => 'date:Y-m-d', // Modified
        'entregado' => 'boolean', // Added
    ];

    /**
     * Relaciones
     */
    public function prescripcion()
    {
        return $this->belongsTo(Prescripcion::class);
    }

    public function paciente() // Added
    {
        return $this->belongsTo(Paciente::class);
    }
    
    public function validador() // Added
    {
        return $this->belongsTo(User::class, 'user_id_validacion');
    }

    public function observacionesHistorial()
    {
        return $this->hasMany(EntregaObservacion::class, 'entrega_id')->orderBy('created_at', 'desc');
    }
}
