<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rombong extends Model
{
    protected $table = 'rombongs';
    protected $primaryKey = 'rombong_id';
    protected $fillable = [
        'user_id',
        'foto_rombong',
        'foto_tetangga_kanan',
        'foto_tetangga_kiri',
        'latitude',
        'longitude',
        'jenis',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
