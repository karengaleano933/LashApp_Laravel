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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_estudio')->default('Oh La Lash by Lorena Ayala Studio');
            $table->string('telefono')->default('+57 300 123 4567');
            $table->string('email_contacto')->default('info@ohlalash.com');
            $table->string('direccion')->default('Calle 123 #45-67, Local 2, Medellín, Colombia');
            $table->string('zona_horaria')->default('America/Bogota');
            $table->string('logo_path')->nullable();
            
            // Preferencias generales
            $table->string('idioma')->default('es');
            $table->string('moneda')->default('COP');
            $table->string('formato_fecha')->default('DD/MM/YYYY');
            $table->string('formato_hora')->default('12');
            $table->boolean('reservas_online')->default(true);
            $table->boolean('recordatorios_auto')->default(true);
            
            // Políticas de citas
            $table->string('antelacion_reservar')->default('2h');
            $table->string('antelacion_cancelar')->default('12h');
            $table->string('tiempo_entre_citas')->default('15m');
            $table->string('duracion_predeterminada')->default('90m');
            $table->text('politica_cancelacion')->nullable();
            
            // Impuestos y copias
            $table->decimal('iva_porcentaje', 5, 2)->default(19.00);
            $table->dateTime('ultima_copia_seguridad')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
