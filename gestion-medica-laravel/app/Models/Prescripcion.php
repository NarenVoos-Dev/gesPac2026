<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescripcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prescripciones';

    protected $fillable = [
        'numero',
        'fecha_prescripcion',
        'fecha_vencimiento',
        'paciente_id',
        'empleado_id',
        'producto_id',
        'cantidad_total',
        'numero_entregas',
        'cantidad_por_entrega',
        'ciudad',
        'municipio',
        'barrio',
        'direccion',
        'diagnostico',
        'indicaciones',
        'observaciones',
        'estado',
        'created_by'
    ];

    protected $casts = [
        'fecha_prescripcion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'prescripcion_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
