<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            
            // Nombres completos
            $table->string('p_nombres'); // Primer nombre
            $table->string('s_nombres')->nullable(); // Segundo nombre
            $table->string('p_apellidos'); // Primer apellido
            $table->string('s_apellidos')->nullable(); // Segundo apellido
            $table->string('nombre_completo')->nullable(); // Generado automáticamente
            
            // Documento
            $table->foreignId('tipo_documento_id')->constrained('tipos_documento');
            $table->string('documento_numero')->unique();
            
            // Información personal
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F', 'Otro']);
            
            // Contacto
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            
            // Ubicación (nombres de la API de Colombia)
            $table->string('departamento')->nullable(); // Nombre del departamento
            $table->string('municipio')->nullable(); // Nombre del municipio
            $table->string('ciudad')->nullable(); // Nombre de la ciudad
            $table->string('codigo_postal')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Estado y auditoría
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Índices
            $table->index(['tipo_documento_id', 'documento_numero']);
            $table->index('is_active');
            $table->index('departamento');
            $table->index('municipio');
            $table->index('ciudad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
