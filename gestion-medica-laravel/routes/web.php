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
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Pacientes
    Route::get('/pacientes', function () {
        return Inertia::render('Pacientes/Index');
    })->name('pacientes.index');

    // Prescripciones
    Route::get('/prescripciones', function () {
        return Inertia::render('Prescripciones/Index');
    })->name('prescripciones.index');

    // Cuentas por Pagar
    Route::get('/cuentas-pagar', function () {
        return Inertia::render('CuentasPagar/Index');
    })->name('cuentas-pagar.index');

    // Cuentas por Cobrar
    Route::get('/cuentas-cobrar', function () {
        return Inertia::render('CuentasCobrar/Index');
    })->name('cuentas-cobrar.index');

    // Configuración
    Route::get('/configuracion', function () {
        return Inertia::render('Configuracion/Index');
    })->name('configuracion.index');

    // Roles y Permisos
    Route::get('/roles', function () {
        return Inertia::render('Roles/Index');
    })->name('roles.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Routes - Tipos de Documento
    Route::prefix('api')->group(function () {
        Route::get('/tipos-documento', [App\Http\Controllers\TipoDocumentoController::class, 'index']);
        Route::post('/tipos-documento', [App\Http\Controllers\TipoDocumentoController::class, 'store']);
        Route::get('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'show']);
        Route::put('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'update']);
        Route::patch('/tipos-documento/{tipoDocumento}/toggle-status', [App\Http\Controllers\TipoDocumentoController::class, 'toggleStatus']);
        Route::delete('/tipos-documento/{tipoDocumento}', [App\Http\Controllers\TipoDocumentoController::class, 'destroy']);

        // Pacientes
        Route::get('/pacientes', [App\Http\Controllers\PacienteController::class, 'index']);
        Route::get('/pacientes/tipos-documento', [App\Http\Controllers\PacienteController::class, 'getTiposDocumento']);
        Route::post('/pacientes', [App\Http\Controllers\PacienteController::class, 'store']);
        Route::get('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'show']);
        Route::put('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'update']);
        Route::patch('/pacientes/{id}/toggle-status', [App\Http\Controllers\PacienteController::class, 'toggleStatus']);
        Route::delete('/pacientes/{id}', [App\Http\Controllers\PacienteController::class, 'destroy']);

        // API Colombia (Proxy para evitar CORS)
        Route::get('/colombia/departments', [App\Http\Controllers\ColombiaApiController::class, 'getDepartments']);
        Route::get('/colombia/departments/{code}/cities', [App\Http\Controllers\ColombiaApiController::class, 'getCitiesByDepartment']);
    });
});

require __DIR__.'/auth.php';

