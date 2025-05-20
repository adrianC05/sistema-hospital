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
        Schema::create('documento_transaccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('users')->onDelete('cascade');
            $table->string('numero')->unique();
            $table->date('fecha');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->string('estado'); // E.g., Pendiente, Pagada, Anulada
            $table->enum('tipo', ['Factura', 'Descargo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_transaccions');
    }
};
