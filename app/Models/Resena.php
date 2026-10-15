<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    protected $table = 'resenas';

    protected $fillable = [
        'cliente_id',
        'cita_id',
        'calificacion',
        'comentario',
        'fecha_resena',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
