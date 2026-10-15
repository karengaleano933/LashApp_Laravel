<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobantes';

    protected $fillable = [
        'pago_id',
        'nombre_archivo',
        'tipo_archivo',
        'ruta_archivo',
        'fecha_subida',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
