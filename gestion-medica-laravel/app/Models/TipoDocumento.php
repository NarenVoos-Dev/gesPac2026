<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasActiveStatus;

    protected $table = 'tipos_documento';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación con pacientes
     */
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'tipo_documento_id');
    }
}
