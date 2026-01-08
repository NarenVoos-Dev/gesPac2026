<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatus
{
    /**
     * Boot the trait
     */
    protected static function bootHasActiveStatus()
    {
        // Scope global para mostrar solo registros activos por defecto
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    /**
     * Scope para incluir registros inactivos
     */
    public function scopeWithInactive(Builder $query)
    {
        return $query->withoutGlobalScope('active');
    }

    /**
     * Scope para obtener solo registros inactivos
     */
    public function scopeOnlyInactive(Builder $query)
    {
        return $query->withoutGlobalScope('active')->where('is_active', false);
    }

    /**
     * Activar el registro
     */
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Desactivar el registro
     */
    public function deactivate()
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Verificar si está activo
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar inactivos
     */
    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }
}
