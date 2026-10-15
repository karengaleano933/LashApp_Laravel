<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'role_id',
    'name',
    'apellido',
    'documento',
    'telefono',
    'email',
    'foto',
    'password',
    'estado',
    'created_by',
    ])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Usuario creador
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Usuarios creados por este usuario
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'user_id');
    }

    public function trabajador()
    {
        return $this->hasOne(Trabajador::class, 'user_id');
    }
}
