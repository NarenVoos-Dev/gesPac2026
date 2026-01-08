<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agente_visitador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('visitador_id')->constrained('empleados')->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['agente_id', 'visitador_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('agente_visitador');
    }
};
