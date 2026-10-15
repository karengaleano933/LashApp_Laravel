<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'cita_id',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
        'fecha_envio',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
