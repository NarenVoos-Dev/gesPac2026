<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitador_profesional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitador_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('empleados')->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['visitador_id', 'profesional_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('visitador_profesional');
    }
};