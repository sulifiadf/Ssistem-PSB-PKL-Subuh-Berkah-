<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name',
        'email',
        'password',
        'alamat',
        'no_telp',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function rombong()
    {
        return $this->hasOne(rombong::class, 'user_id', 'user_id');
    }

    public function kehadiran()
    {
        return $this->hasMany(kehadiran::class, 'user_id', 'user_id');
    }

    public function registrasi_pertanyaan()
    {
        return $this->hasOne(registrasi_pertanyaan::class, 'user_id', 'user_id');
    }
    public function waiting_list()
    {
        return $this->hasMany(waiting_list::class, 'user_id', 'user_id');
    }
}
