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
        Schema::create('historial_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')
                ->constrained('citas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('estado_anterior');
            $table->string('estado_nuevo');
            $table->foreignId('modificado_por')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->dateTime('fecha_cambio');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_citas');
    }
};
