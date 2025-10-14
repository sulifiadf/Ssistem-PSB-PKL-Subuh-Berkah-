<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class kehadiran extends Model
{
    protected $table = 'kehadirans';
    protected $primaryKey = 'kehadiran_id';
    protected $fillable = [
        'user_id',
        'tanggal',
        'status',
        'waktu_konfirmasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_konfirmasi' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function rombong()
    {
        return $this->hasOne(rombong::class, 'user_id', 'user_id');
    }
}
