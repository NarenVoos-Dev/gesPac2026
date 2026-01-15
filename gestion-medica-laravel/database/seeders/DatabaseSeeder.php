<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar seeders en orden de dependencias
        $this->call([
            // 1. Roles y permisos (Spatie)
            RolePermissionSeeder::class,
            
            // 2. Catálogos básicos
            TiposDocumentoSeeder::class,
            CargoSeeder::class,
            
            // 3. Otros seeders (agregar aquí cuando se creen)
            // EspecialidadSeeder::class,
            // EmpleadoSeeder::class,
        ]);

        $this->command->info('✅ Todos los seeders ejecutados correctamente');
    }
}
