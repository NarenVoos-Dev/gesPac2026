Propuesta de Migraciones - Sistema de Gestión Médica
📋 Resumen del Esquema
Este documento presenta el esquema completo de base de datos para el sistema de gestión médica, organizado por módulos.

🔐 Módulo de Autenticación y Usuarios
1. Tabla: users (Ya existe con Laravel Breeze)
Extenderemos la tabla existente con campos adicionales.

Migración adicional:

Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
    $table->string('document_type')->nullable(); // DNI, Pasaporte, etc.
    $table->string('document_number')->unique()->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
});
2. Tabla: roles
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // admin, doctor, visitador, contador
    $table->string('display_name');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
3. Tabla: permissions
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // pacientes.create, prescripciones.view
    $table->string('display_name');
    $table->string('module'); // pacientes, prescripciones, etc.
    $table->text('description')->nullable();
    $table->timestamps();
});
4. Tabla: role_user (Pivot)
Schema::create('role_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('role_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
5. Tabla: permission_role (Pivot)
Schema::create('permission_role', function (Blueprint $table) {
    $table->id();
    $table->foreignId('role_id')->constrained()->onDelete('cascade');
    $table->foreignId('permission_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
👥 Módulo de Pacientes
6. Tabla: pacientes
Schema::create('pacientes', function (Blueprint $table) {
    $table->id();
    $table->string('p_nombres');
    $table->string('s_nombres')->nullable();
    $table->string('p_apellidos');
    $table->string('s_apellidos')->nullable();
    $table->string('nombre_completo')->nullable();
    $table->string('id_documento_tipo'); // DNI, Pasaporte, etc.
    $table->string('documento_numero')->unique();
    $table->date('fecha_nacimiento');
    $table->enum('sexo', ['M', 'F', 'Otro']);
    $table->string('telefono')->nullable();
    $table->string('email')->nullable();
    $table->text('direccion')->nullable();
    $table->string('ciudad')->nullable();
    $table->string('provincia')->nullable();
    $table->string('codigo_postal')->nullable();
    $table->text('observaciones')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
👨‍⚕️ Módulo de Profesionales
7. Tabla: especialidades
Schema::create('especialidades', function (Blueprint $table) {
    $table->id();
    $table->string('nombre')->unique();
    $table->text('descripcion')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
8. Tabla: profesionales
Schema::create('profesionales', function (Blueprint $table) {
    $table->id();
    $table->string('codigo')->unique(); // PROF-0001
    $table->string('nombres');
    $table->string('apellidos');
    $table->string('documento_tipo');
    $table->string('documento_numero')->unique();
    $table->string('matricula_profesional')->unique();
    $table->foreignId('especialidad_id')->constrained('especialidades');
    $table->string('telefono')->nullable();
    $table->string('email')->nullable();
    $table->text('direccion')->nullable();
    $table->decimal('comision_porcentaje', 5, 2)->default(0); // 0.00 a 100.00
    $table->text('observaciones')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
💊 Módulo de Productos
9. Tabla: categorias_productos
Schema::create('categorias_productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre')->unique();
    $table->text('descripcion')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
10. Tabla: productos
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('codigo')->unique(); // PROD-0001
    $table->string('nombre');
    $table->text('descripcion')->nullable();
    $table->foreignId('categoria_id')->constrained('categorias_productos');
    $table->string('presentacion')->nullable(); // Caja x 30, Frasco x 100ml
    $table->decimal('precio_venta', 10, 2);
    $table->decimal('precio_costo', 10, 2)->nullable();
    $table->integer('stock_actual')->default(0);
    $table->integer('stock_minimo')->default(0);
    $table->decimal('comision_porcentaje', 5, 2)->default(0);
    $table->boolean('requiere_receta')->default(false);
    $table->text('observaciones')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
📋 Módulo de Prescripciones
11. Tabla: prescripciones
Schema::create('prescripciones', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // PRESC-2024-0001
    $table->date('fecha');
    $table->foreignId('paciente_id')->constrained('pacientes');
    $table->foreignId('profesional_id')->constrained('profesionales');
    $table->text('diagnostico')->nullable();
    $table->text('indicaciones')->nullable();
    $table->decimal('subtotal', 10, 2)->default(0);
    $table->decimal('comision_total', 10, 2)->default(0);
    $table->decimal('total', 10, 2)->default(0);
    $table->enum('estado', ['pendiente', 'parcial', 'cancelada', 'finalizada'])->default('pendiente');
    $table->text('observaciones')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
12. Tabla: prescripcion_detalles
Schema::create('prescripcion_detalles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('prescripcion_id')->constrained('prescripciones')->onDelete('cascade');
    $table->foreignId('producto_id')->constrained('productos');
    $table->integer('cantidad');
    $table->decimal('precio_unitario', 10, 2);
    $table->decimal('subtotal', 10, 2);
    $table->decimal('comision_porcentaje', 5, 2);
    $table->decimal('comision_monto', 10, 2);
    $table->text('indicaciones')->nullable();
    $table->timestamps();
});
🚚 Módulo de Entregas
13. Tabla: visitadores
Schema::create('visitadores', function (Blueprint $table) {
    $table->id();
    $table->string('codigo')->unique(); // VIS-0001
    $table->string('nombres');
    $table->string('apellidos');
    $table->string('documento_tipo');
    $table->string('documento_numero')->unique();
    $table->string('telefono')->nullable();
    $table->string('email')->nullable();
    $table->string('zona_asignada')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
14. Tabla: entregas
Schema::create('entregas', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // ENT-2024-0001
    $table->foreignId('prescripcion_id')->constrained('prescripciones');
    $table->foreignId('visitador_id')->nullable()->constrained('visitadores');
    $table->date('fecha_programada');
    $table->date('fecha_entrega')->nullable();
    $table->time('hora_entrega')->nullable();
    $table->enum('estado', ['programada', 'en_ruta', 'entregada', 'fallida', 'cancelada'])->default('programada');
    $table->text('direccion_entrega');
    $table->string('receptor_nombre')->nullable();
    $table->string('receptor_documento')->nullable();
    $table->text('observaciones')->nullable();
    $table->text('motivo_falla')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
15. Tabla: entrega_detalles
Schema::create('entrega_detalles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('entrega_id')->constrained('entregas')->onDelete('cascade');
    $table->foreignId('producto_id')->constrained('productos');
    $table->integer('cantidad_programada');
    $table->integer('cantidad_entregada')->default(0);
    $table->text('observaciones')->nullable();
    $table->timestamps();
});
💰 Módulo Financiero
16. Tabla: cuentas_por_pagar
Schema::create('cuentas_por_pagar', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // CPP-2024-0001
    $table->foreignId('profesional_id')->constrained('profesionales');
    $table->foreignId('prescripcion_id')->nullable()->constrained('prescripciones');
    $table->date('fecha_emision');
    $table->date('fecha_vencimiento');
    $table->decimal('monto', 10, 2);
    $table->decimal('monto_pagado', 10, 2)->default(0);
    $table->decimal('saldo', 10, 2);
    $table->enum('estado', ['pendiente', 'parcial', 'pagada', 'vencida'])->default('pendiente');
    $table->string('concepto');
    $table->text('observaciones')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
17. Tabla: pagos_profesionales
Schema::create('pagos_profesionales', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // PAG-2024-0001
    $table->foreignId('cuenta_por_pagar_id')->constrained('cuentas_por_pagar');
    $table->date('fecha_pago');
    $table->decimal('monto', 10, 2);
    $table->enum('metodo_pago', ['efectivo', 'transferencia', 'cheque', 'otro']);
    $table->string('referencia')->nullable();
    $table->text('observaciones')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
});
18. Tabla: cuentas_por_cobrar
Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // CPC-2024-0001
    $table->foreignId('paciente_id')->constrained('pacientes');
    $table->foreignId('prescripcion_id')->nullable()->constrained('prescripciones');
    $table->date('fecha_emision');
    $table->date('fecha_vencimiento');
    $table->decimal('monto', 10, 2);
    $table->decimal('monto_cobrado', 10, 2)->default(0);
    $table->decimal('saldo', 10, 2);
    $table->enum('estado', ['pendiente', 'parcial', 'cobrada', 'vencida'])->default('pendiente');
    $table->string('concepto');
    $table->text('observaciones')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
19. Tabla: cobros_pacientes
Schema::create('cobros_pacientes', function (Blueprint $table) {
    $table->id();
    $table->string('numero')->unique(); // COB-2024-0001
    $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar');
    $table->date('fecha_cobro');
    $table->decimal('monto', 10, 2);
    $table->enum('metodo_cobro', ['efectivo', 'transferencia', 'tarjeta', 'otro']);
    $table->string('referencia')->nullable();
    $table->text('observaciones')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
});
📊 Módulo de Configuración
20. Tabla: configuraciones -- NO 
Schema::create('configuraciones', function (Blueprint $table) {
    $table->id();
    $table->string('clave')->unique();
    $table->text('valor')->nullable();
    $table->string('tipo')->default('string'); // string, number, boolean, json
    $table->string('grupo')->nullable(); // general, facturacion, notificaciones
    $table->text('descripcion')->nullable();
    $table->timestamps();
});
21. Tabla: audit_logs (Auditoría)
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users');
    $table->string('action'); // create, update, delete
    $table->string('model'); // Paciente, Prescripcion, etc.
    $table->unsignedBigInteger('model_id');
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
});
🔑 Índices y Relaciones Importantes
Índices recomendados:
// En pacientes
$table->index(['documento_tipo', 'documento_numero']);
$table->index('is_active');
// En prescripciones
$table->index(['paciente_id', 'fecha']);
$table->index(['profesional_id', 'fecha']);
$table->index('estado');
// En entregas
$table->index(['visitador_id', 'fecha_programada']);
$table->index('estado');
// En cuentas por pagar/cobrar
$table->index(['estado', 'fecha_vencimiento']);
📝 Seeders Iniciales
RolesSeeder
- Admin (Acceso total)
- Doctor (Gestión de prescripciones)
- Visitador (Gestión de entregas)
- Contador (Módulo financiero)
- Agente (Gestión de pacientes)
PermissionsSeeder
- pacientes.* (create, read, update, delete)
- prescripciones.* (create, read, update, delete)
- entregas.* (create, read, update, delete)
- cuentas.* (create, read, update, delete)
- reportes.* (view, export)
- configuracion.* (read, update)
✅ Validaciones y Reglas de Negocio
Pacientes: Documento único,
Profesionales:  comisión entre 0-100%
Productos: Stock no negativo, precio > 0
Prescripciones: Calcular automáticamente comisiones
Entregas: Solo productos de la prescripción asociada
Cuentas: Saldo = Monto - Monto Pagado/Cobrado
🚀 Orden de Ejecución de Migraciones
Usuarios y Roles (1-5)
Pacientes (6)
Profesionales (7-8)
Productos (9-10)
Prescripciones (11-12)
Entregas (13-15)
Financiero (16-19)
Configuración (20-21)
Esta propuesta cubre todos los módulos del sistema con relaciones bien definidas y campos necesarios para la gestión médica completa.