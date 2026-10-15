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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')
                ->constrained('citas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('metodo_pago');
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['pendiente','completado','rechazado'])->default('pendiente');
            $table->dateTime('fecha_pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
