<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'trabajador_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'horario_id');
    }
}
