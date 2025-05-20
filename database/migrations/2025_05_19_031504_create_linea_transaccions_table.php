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
        Schema::create('linea_transaccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_transaccion_id')->constrained('documento_transaccions')->onDelete('cascade');
            $table->morphs('serviceable'); // Crea serviceable_id (unsignedBigInteger) y serviceable_type (string)
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linea_transaccions');
    }
};
