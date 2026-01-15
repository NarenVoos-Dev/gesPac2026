<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaObservacion extends Model
{
    use HasFactory;

    protected $table = 'entrega_observaciones';

    protected $fillable = [
        'entrega_id',
        'user_id',
        'observacion',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
