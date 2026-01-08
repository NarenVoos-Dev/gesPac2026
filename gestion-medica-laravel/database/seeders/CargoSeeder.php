<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            [
                'codigo' => 'AGENTE',
                'nombre' => 'Agente',
                'descripcion' => 'Agente de call center o recepcionista',
                'is_active' => true,
            ],
            [
                'codigo' => 'VISITADOR_MEDICO',
                'nombre' => 'Visitador Médico',
                'descripcion' => 'Visitador médico que gestiona profesionales de la salud',
                'is_active' => true,
            ],
            [
                'codigo' => 'PROFESIONAL_SALUD',
                'nombre' => 'Profesional de la Salud',
                'descripcion' => 'Médico, enfermero u otro profesional de la salud',
                'is_active' => true,
            ],
        ];

        foreach ($cargos as $cargo) {
            Cargo::create($cargo);
        }

        $this->command->info('Cargos creados exitosamente!');
    }
}
