<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'cita_id',
        'metodo_pago',
        'monto',
        'estado',
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'pago_id');
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'pago_id')->latestOfMany();
    }
}
