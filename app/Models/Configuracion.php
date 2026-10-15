<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre_estudio',
        'telefono',
        'email_contacto',
        'direccion',
        'zona_horaria',
        'logo_path',
        'idioma',
        'moneda',
        'formato_fecha',
        'formato_hora',
        'reservas_online',
        'recordatorios_auto',
        'antelacion_reservar',
        'antelacion_cancelar',
        'tiempo_entre_citas',
        'duracion_predeterminada',
        'politica_cancelacion',
        'iva_porcentaje',
        'ultima_copia_seguridad',
    ];

    protected $casts = [
        'reservas_online' => 'boolean',
        'recordatorios_auto' => 'boolean',
        'iva_porcentaje' => 'decimal:2',
        'ultima_copia_seguridad' => 'datetime',
    ];

    /**
     * Get or create the singleton configuration instance.
     */
    public static function getInstancia(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'nombre_estudio' => 'Oh La Lash by Lorena Ayala Studio',
                'telefono' => '+57 300 123 4567',
                'email_contacto' => 'info@ohlalash.com',
                'direccion' => 'Calle 123 #45-67, Local 2, Medellín, Colombia',
                'zona_horaria' => 'America/Bogota',
                'idioma' => 'es',
                'moneda' => 'COP',
                'formato_fecha' => 'DD/MM/YYYY',
                'formato_hora' => '12',
                'reservas_online' => true,
                'recordatorios_auto' => true,
                'antelacion_reservar' => '2h',
                'antelacion_cancelar' => '12h',
                'tiempo_entre_citas' => '15m',
                'duracion_predeterminada' => '90m',
                'politica_cancelacion' => 'Las cancelaciones deben realizarse con mínimo 12 horas de anticipación para evitar cargos adicionales.',
                'iva_porcentaje' => 19.00,
                'ultima_copia_seguridad' => now()->subDay(),
            ]
        );
    }
}
