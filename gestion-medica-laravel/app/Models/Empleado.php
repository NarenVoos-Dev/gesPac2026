<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombres',
        'apellidos',
        'tipo_documento_id',
        'documento_numero',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'email_personal',
        'email_corporativo',
        'direccion',
        'ciudad',
        'departamento',
        'foto_url',
        'fecha_ingreso',
        'fecha_salida',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    // Accessor: Nombre completo
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    // Relación: Usuario (un empleado puede tener un usuario)
    public function user()
    {
        return $this->hasOne(User::class, 'empleado_id');
    }

    // Relación: Tipo de documento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    // Relación: Cargo directo (campo cargo_id)
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    // Relación: Cargos (muchos a muchos - tabla pivot)
    public function cargos()
    {
        return $this->belongsToMany(Cargo::class, 'empleado_cargo')
            ->withPivot('es_principal', 'fecha_asignacion', 'fecha_fin', 'observaciones')
            ->withTimestamps();
    }

    // Relación: Cargo principal (desde tabla pivot)
    public function cargoPrincipal()
    {
        return $this->belongsToMany(Cargo::class, 'empleado_cargo')
            ->wherePivot('es_principal', true)
            ->wherePivotNull('fecha_fin')
            ->withPivot('es_principal', 'fecha_asignacion', 'fecha_fin', 'observaciones')
            ->withTimestamps();
    }

    // Relación: Especialidades (muchos a muchos - para profesionales)
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'empleado_especialidad')
            ->withTimestamps();
    }

    // Relación: Agentes asignados (cuando el empleado es visitador)
    public function agentes()
    {
        return $this->belongsToMany(Empleado::class, 'agente_visitador', 'visitador_id', 'agente_id')
            ->withTimestamps();
    }

    // Relación: Visitadores asignados (cuando el empleado es agente)
    public function visitadores()
    {
        return $this->belongsToMany(Empleado::class, 'agente_visitador', 'agente_id', 'visitador_id')
            ->withTimestamps();
    }

    // Relación: Profesionales asignados (cuando el empleado es visitador)
    public function profesionales()
    {
        return $this->belongsToMany(Empleado::class, 'visitador_profesional', 'visitador_id', 'profesional_id')
            ->withTimestamps();
    }

    // Relación: Visitadores asignados (cuando el empleado es profesional)
    public function visitadoresAsignados()
    {
        return $this->belongsToMany(Empleado::class, 'visitador_profesional', 'profesional_id', 'visitador_id')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active');
    }

    public function scopeConCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    // Métodos de ayuda
    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->fecha_salida = now();
        $this->save();

        // Desactivar usuario también
        if ($this->user) {
            $this->user->is_active = false;
            $this->user->save();
        }
    }

    public function asignarCargo($cargoId, $esPrincipal = false)
    {
        // Si es principal, marcar otros como no principales
        if ($esPrincipal) {
            $this->cargos()->updateExistingPivot(
                $this->cargos()->pluck('cargos.id')->toArray(),
                ['es_principal' => false]
            );
        }

        $this->cargos()->attach($cargoId, [
            'es_principal' => $esPrincipal,
            'fecha_asignacion' => now(),
        ]);
    }
}
