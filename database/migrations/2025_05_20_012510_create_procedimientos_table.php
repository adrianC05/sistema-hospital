<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimientos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre'); // Descripción principal
            $table->decimal('precio', 10, 2);
            $table->text('detalles')->nullable(); // Para detalles del procedimiento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimientos');
    }
};