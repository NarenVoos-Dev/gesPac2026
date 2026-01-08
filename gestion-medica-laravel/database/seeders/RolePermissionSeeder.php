<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear Permisos
        $permissions = [
            // Pacientes
            'pacientes.ver',
            'pacientes.crear',
            'pacientes.editar',
            'pacientes.eliminar',

            // Empleados
            'empleados.ver',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',

            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'usuarios.asignar-roles',

            // Configuración
            'configuracion.ver',
            'configuracion.editar',

            // Cargos
            'cargos.ver',
            'cargos.crear',
            'cargos.editar',
            'cargos.eliminar',

            // Especialidades
            'especialidades.ver',
            'especialidades.crear',
            'especialidades.editar',
            'especialidades.eliminar',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear Roles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $agente = Role::create(['name' => 'agente']);
        $visitador = Role::create(['name' => 'visitador']);
        $profesional = Role::create(['name' => 'profesional']);

        // Asignar permisos a Super Admin (todos)
        $superAdmin->givePermissionTo(Permission::all());

        // Asignar permisos a Admin
        $admin->givePermissionTo([
            'pacientes.ver',
            'pacientes.crear',
            'pacientes.editar',
            'pacientes.eliminar',
            'empleados.ver',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'configuracion.ver',
            'configuracion.editar',
            'cargos.ver',
            'cargos.crear',
            'cargos.editar',
            'especialidades.ver',
            'especialidades.crear',
            'especialidades.editar',
        ]);

        // Asignar permisos a Agente
        $agente->givePermissionTo([
            'pacientes.ver',
            'pacientes.crear',
            'pacientes.editar',
        ]);

        // Visitador y Profesional (para futuro)
        // Por ahora sin permisos, se agregarán cuando se implementen sus módulos

        $this->command->info('Roles y permisos creados exitosamente!');
    }
}
