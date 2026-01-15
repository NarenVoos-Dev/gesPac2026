<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionProducto extends Model
{
    protected $table = 'comisiones_productos';

    protected $fillable = [
        'empleado_id',
        'producto_id',
        'tipo_calculo', // PORCENTAJE, FIJO
        'valor',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
