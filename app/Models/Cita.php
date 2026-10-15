<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    public $timestamps = false; // La tabla usa fecha_creacion en vez de created_at/updated_at

    protected $fillable = [
        'cliente_id',
        'trabajador_id',
        'servicio_id',
        'horario_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'observaciones',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_creacion' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'cita_id')->latestOfMany();
    }

    public function resena()
    {
        return $this->hasOne(Resena::class, 'cita_id');
    }
}
