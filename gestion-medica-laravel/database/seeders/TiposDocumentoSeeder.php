<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TiposDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposDocumento = [
            [
                'codigo' => 'CC',
                'nombre' => 'Cédula de Ciudadanía',
                'descripcion' => 'Documento de identidad para ciudadanos colombianos mayores de 18 años',
                'is_active' => true,
            ],
            [
                'codigo' => 'TI',
                'nombre' => 'Tarjeta de Identidad',
                'descripcion' => 'Documento de identidad para menores de 18 años',
                'is_active' => true,
            ],
            [
                'codigo' => 'CE',
                'nombre' => 'Cédula de Extranjería',
                'descripcion' => 'Documento de identidad para extranjeros residentes en Colombia',
                'is_active' => true,
            ],
            [
                'codigo' => 'PA',
                'nombre' => 'Pasaporte',
                'descripcion' => 'Documento de viaje internacional',
                'is_active' => true,
            ],
            [
                'codigo' => 'RC',
                'nombre' => 'Registro Civil',
                'descripcion' => 'Documento de identidad para menores de 7 años',
                'is_active' => true,
            ],
            [
                'codigo' => 'PEP',
                'nombre' => 'Permiso Especial de Permanencia',
                'descripcion' => 'Documento para migrantes venezolanos',
                'is_active' => true,
            ],
        ];

        foreach ($tiposDocumento as $tipo) {
            TipoDocumento::create($tipo);
        }
    }
}
