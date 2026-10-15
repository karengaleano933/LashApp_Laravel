<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'duracion_minuto',
        'estado',
        'imagen_url',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'servicio_id');
    }
}
