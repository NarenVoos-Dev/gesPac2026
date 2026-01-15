<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Tabla Prescripciones
        Schema::create('prescripciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // PRE-202X-0001
            $table->date('fecha_prescripcion');
            $table->date('fecha_vencimiento')->nullable();
            
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('empleado_id')->constrained('empleados'); // Profesional que formula
            $table->foreignId('producto_id')->constrained('productos');
            
            $table->integer('cantidad_total');
            $table->integer('numero_entregas')->default(1);
            $table->integer('cantidad_por_entrega')->nullable(); // Helper
            
            // Ubicación (Copia del paciente o específica)
            $table->string('ciudad')->nullable();
            $table->string('municipio')->nullable();
            $table->string('barrio')->nullable();
            $table->text('direccion')->nullable();
            
            $table->text('diagnostico')->nullable();
            $table->text('indicaciones')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->enum('estado', ['ACTIVA', 'COMPLETADA', 'VENCIDA', 'ANULADA'])->default('ACTIVA');
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabla Entregas (Cronograma)
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescripcion_id')->constrained('prescripciones')->onDelete('cascade');
            
            $table->integer('numero_entrega'); // 1, 2, 3...
            $table->integer('cantidad_programada');
            
            $table->date('fecha_programada');
            $table->date('fecha_entrega_real')->nullable();
            $table->date('proxima_fecha_validacion')->nullable(); // Para llamada agente
            
            $table->enum('estado', ['PENDIENTE', 'PROGRAMADA', 'EN_RUTA', 'ENTREGADO', 'NO_ENTREGADO', 'CANCELADA'])->default('PENDIENTE');
            
            // Campos de validación de entrega (Solicitud usuario)
            $table->boolean('entregado')->default(false); // Check de validación
            $table->integer('cantidad_entregada_real')->nullable(); // Para ajustes si la entrega varía de la programada
            $table->foreignId('user_id_validacion')->nullable()->constrained('users'); // Quién validó la entrega en sistema
            
            $table->text('observaciones')->nullable(); // Observación rápida del estado
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Tabla Observaciones de Entregas (Historial)
        Schema::create('entrega_observaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrega_id')->constrained('entregas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->text('observacion');
            $table->timestamps(); // Created_at es la fecha de observación
        });
    }

    public function down()
    {
        Schema::dropIfExists('entrega_observaciones');
        Schema::dropIfExists('entregas');
        Schema::dropIfExists('prescripciones');
    }
};
