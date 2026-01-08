<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasActiveStatus;

    protected $table = 'pacientes';

    protected $fillable = [
        'p_nombres',
        's_nombres',
        'p_apellidos',
        's_apellidos',
        'nombre_completo',
        'tipo_documento_id',
        'documento_numero',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email',
        'direccion',
        'departamento',
        'municipio',
        'ciudad',
        'codigo_postal',
        'observaciones',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'edad',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar nombre completo automáticamente
        static::saving(function ($paciente) {
            $paciente->nombre_completo = trim(
                $paciente->p_nombres . ' ' .
                ($paciente->s_nombres ?? '') . ' ' .
                $paciente->p_apellidos . ' ' .
                ($paciente->s_apellidos ?? '')
            );
        });
    }

    /**
     * Relación con tipo de documento
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    /**
     * Relación con usuario que creó
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con usuario que actualizó
     */
    public function actualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor para calcular edad
     */
    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    /**
     * Scope para buscar por nombre o documento
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre_completo', 'like', "%{$search}%")
              ->orWhere('documento_numero', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
