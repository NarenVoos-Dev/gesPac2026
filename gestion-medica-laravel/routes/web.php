<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Todos pueden ver)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Módulos Clínicos (Requieren permisos específicos)
    Route::group(['middleware' => []], function () {
        Route::get('/pacientes', function () {
            return Inertia::render('Pacientes/Index');
        })->name('pacientes.index');
    });

    Route::group(['middleware' => []], function () {
        Route::get('/prescripciones', function () {
            return Inertia::render('Prescripciones/Index');
        })->name('prescripciones.index');
    });

    Route::group(['middleware' => []], function () {
        Route::get('/entregas', function () {
            return Inertia::render('Entregas/Index');
        })->name('entregas.index');
    });

    // Módulos Administrativos
    Route::group(['middleware' => []], function () {
        Route::get('/cuentas-pagar', function () {
            return Inertia::render('CuentasPagar/Index');
        })->name('cuentas-pagar.index');
    });

    Route::group(['middleware' => []], function () {
        Route::get('/cuentas-cobrar', function () {
            return Inertia::render('CuentasCobrar/Index');
        })->name('cuentas-cobrar.index');
    });

    // Configuración, Usuarios y Roles (Administración)
    Route::group(['middleware' => []], function () {
        Route::get('/configuracion', function () {
            return Inertia::render('Configuracion/Index');
        })->name('configuracion.index');
    });

    Route::group(['middleware' => []], function () {
        Route::get('/roles', function () {
            return Inertia::render('Roles/Index');
        })->name('roles.index');
    });

    Route::group(['middleware' => []], function () {
        Route::get('/usuarios', function () {
            return Inertia::render('Usuarios/Index');
        })->name('usuarios.index');
    });

    // Entregas (Web routes)
    Route::put('/entregas/{entrega}', [EntregaController::class, 'update'])->name('entregas.update');

    // Profile (Todos)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Routes
    Route::prefix('api')->group(function () {
        // Tipos de Documento
        Route::get('/tipos-documento', [App\Http\Controllers\TipoDocumentoController::class, 'index']);
        Route::middleware([])->post('/tipos-documento', [App\Http\Controllers\TipoDocumentoController::class, 'store']);
        Route::middleware([])->put('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'update']);
        Route::middleware([])->patch('/tipos-documento/{tipoDocumento}/toggle-status', [App\Http\Controllers\TipoDocumentoController::class, 'toggleStatus']);
        Route::middleware([])->delete('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'destroy']);
        Route::get('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'show']);

        // Productos
        // Rutas API para gestión de productos (sin middleware activo por ahora)
        Route::middleware([])->get('/productos', [App\Http\Controllers\ProductoController::class, 'index']);
        Route::middleware([])->post('/productos', [App\Http\Controllers\ProductoController::class, 'store']);
        Route::middleware([])->put('/productos/{id}', [App\Http\Controllers\ProductoController::class, 'update']);
        Route::middleware([])->patch('/productos/{id}/toggle-status', [App\Http\Controllers\ProductoController::class, 'toggleStatus']);
        Route::middleware([])->delete('/productos/{id}', [App\Http\Controllers\ProductoController::class, 'destroy']);
        Route::middleware([])->patch('/productos/{id}/restore', [App\Http\Controllers\ProductoController::class, 'restore']);
        Route::get('/productos/{id}', [App\Http\Controllers\ProductoController::class, 'show']);

        // Comisiones Productos
        Route::apiResource('comisiones-productos', App\Http\Controllers\ComisionProductoController::class);

        // Prescripciones
        Route::apiResource('prescripciones', App\Http\Controllers\PrescripcionController::class);

        // Entregas
        Route::put('/entregas/{entrega}', [App\Http\Controllers\EntregaController::class, 'update']); 
        Route::post('/entregas', [App\Http\Controllers\EntregaController::class, 'store']); 

        // Pacientes
        Route::middleware([])->group(function () {
            Route::get('/pacientes', [App\Http\Controllers\PacienteController::class, 'index']);
            Route::get('/pacientes/tipos-documento', [App\Http\Controllers\PacienteController::class, 'getTiposDocumento']);
            Route::get('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'show']);
        });
        Route::middleware([])->post('/pacientes', [App\Http\Controllers\PacienteController::class, 'store']);
        Route::middleware([])->put('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'update']);
        Route::middleware([])->patch('/pacientes/{id}/toggle-status', [App\Http\Controllers\PacienteController::class, 'toggleStatus']);
        Route::middleware([])->delete('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'destroy']);

        // Cargos
        Route::get('/cargos/etiquetas-sistema', [App\Http\Controllers\CargoController::class, 'getEtiquetasSistema']);
        Route::middleware([])->get('/cargos', [App\Http\Controllers\CargoController::class, 'index']);
        Route::middleware([])->post('/cargos', [App\Http\Controllers\CargoController::class, 'store']);
        Route::middleware([])->put('/cargos/{cargo}', [App\Http\Controllers\CargoController::class, 'update']);
        Route::middleware([])->delete('/cargos/{cargo}', [App\Http\Controllers\CargoController::class, 'destroy']);
        Route::middleware([])->patch('/cargos/{id}/restore', [App\Http\Controllers\CargoController::class, 'restore']);
        Route::get('/cargos/{cargo}', [App\Http\Controllers\CargoController::class, 'show']);

        // Especialidades
        Route::middleware([])->get('/especialidades', [App\Http\Controllers\EspecialidadController::class, 'index']);
        Route::middleware([])->post('/especialidades', [App\Http\Controllers\EspecialidadController::class, 'store']);
        Route::middleware([])->put('/especialidades/{especialidad}', [App\Http\Controllers\EspecialidadController::class, 'update']);
        Route::middleware([])->delete('/especialidades/{especialidad}', [App\Http\Controllers\EspecialidadController::class, 'destroy']);
        Route::middleware([])->patch('/especialidades/{id}/restore', [App\Http\Controllers\EspecialidadController::class, 'restore']);
        Route::get('/especialidades/{especialidad}', [App\Http\Controllers\EspecialidadController::class, 'show']);

        // Empleados
        Route::middleware([])->get('/empleados', [App\Http\Controllers\EmpleadoController::class, 'index']);
        Route::middleware([])->post('/empleados', [App\Http\Controllers\EmpleadoController::class, 'store']);
        Route::middleware([])->put('/empleados/{empleado}', [App\Http\Controllers\EmpleadoController::class, 'update']);
        Route::middleware([])->delete('/empleados/{empleado}', [App\Http\Controllers\EmpleadoController::class, 'destroy']);
        Route::middleware([])->patch('/empleados/{id}/restore', [App\Http\Controllers\EmpleadoController::class, 'restore']);
        Route::get('/empleados/{empleado}', [App\Http\Controllers\EmpleadoController::class, 'show']);
        
        // Empleados - Helpers y Asignaciones (requieren editar empleado)
        Route::middleware([])->group(function () {
            Route::post('/empleados/{id}/asignar-cargo', [App\Http\Controllers\EmpleadoController::class, 'asignarCargo']);
            Route::post('/empleados/{id}/asignar-especialidades', [App\Http\Controllers\EmpleadoController::class, 'asignarEspecialidades']);
            Route::post('/empleados/{id}/asignar-agentes', [App\Http\Controllers\EmpleadoController::class, 'asignarAgentes']);
            Route::post('/empleados/{id}/asignar-profesionales', [App\Http\Controllers\EmpleadoController::class, 'asignarProfesionales']);
            Route::get('/empleados/{id}/visitadores', [App\Http\Controllers\EmpleadoController::class, 'obtenerVisitadores']);
            Route::get('/empleados/{id}/profesionales', [App\Http\Controllers\EmpleadoController::class, 'obtenerProfesionales']);
            Route::get('/empleados-cargos', [App\Http\Controllers\EmpleadoController::class, 'getCargos']);
            Route::post('/empleados/{id}/crear-usuario', [App\Http\Controllers\EmpleadoController::class, 'crearUsuario']);
        });

        // Usuarios
        Route::middleware([])->get('/usuarios', [App\Http\Controllers\UserController::class, 'index']);
        Route::middleware([])->post('/usuarios', [App\Http\Controllers\UserController::class, 'store']);
        Route::middleware([])->group(function () {
            Route::get('/usuarios/{id}', [App\Http\Controllers\UserController::class, 'show']);
            Route::put('/usuarios/{id}', [App\Http\Controllers\UserController::class, 'update']);
            Route::patch('/usuarios/{id}/rol', [App\Http\Controllers\UserController::class, 'updateRole']);
            Route::patch('/usuarios/{id}/toggle-status', [App\Http\Controllers\UserController::class, 'toggleStatus']);
            Route::post('/usuarios/{id}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword']);
        });

        // Roles y Permisos (Admin estricto o permisos específicos)
        Route::middleware([])->get('/roles', [App\Http\Controllers\RoleController::class, 'index']);
        Route::middleware([])->post('/roles', [App\Http\Controllers\RoleController::class, 'store']);
        Route::middleware([])->group(function () {
            Route::put('/roles/{id}', [App\Http\Controllers\RoleController::class, 'update']);
            Route::get('/roles/{id}/permissions', [App\Http\Controllers\RoleController::class, 'permissions']);
            Route::post('/roles/{id}/sync-permissions', [App\Http\Controllers\RoleController::class, 'syncPermissions']);
        });
        Route::middleware([])->delete('/roles/{id}', [App\Http\Controllers\RoleController::class, 'destroy']);

        Route::middleware([])->get('/permissions', [App\Http\Controllers\PermissionController::class, 'index']);
        
        // Asignaciones (Requiere permisos de configuración/empleados)
        Route::middleware([])->group(function () {
            Route::get('/asignaciones/agentes', [App\Http\Controllers\AsignacionController::class, 'getAgentes']);
            Route::get('/asignaciones/visitadores-disponibles', [App\Http\Controllers\AsignacionController::class, 'getVisitadoresDisponibles']);
            Route::get('/asignaciones/agentes/{id}/visitadores', [App\Http\Controllers\AsignacionController::class, 'getVisitadoresAsignados']);
            Route::post('/asignaciones/agentes/{id}/visitadores', [App\Http\Controllers\AsignacionController::class, 'asignarVisitadores']);
            Route::get('/asignaciones/visitadores', [App\Http\Controllers\AsignacionController::class, 'getVisitadores']);
            Route::get('/asignaciones/profesionales-disponibles', [App\Http\Controllers\AsignacionController::class, 'getProfesionalesDisponibles']);
            Route::get('/asignaciones/visitadores/{id}/profesionales', [App\Http\Controllers\AsignacionController::class, 'getProfesionalesAsignados']);
            Route::post('/asignaciones/visitadores/{id}/profesionales', [App\Http\Controllers\AsignacionController::class, 'asignarProfesionales']);
        });

        // API Colombia (Proxy para evitar CORS)
        Route::get('/colombia/departments', [App\Http\Controllers\ColombiaApiController::class, 'getDepartments']);
        Route::get('/colombia/departments/{code}/cities', [App\Http\Controllers\ColombiaApiController::class, 'getCitiesByDepartment']);
    });
});

require __DIR__.'/auth.php';
