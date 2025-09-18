<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rombong extends Model
{
    protected $table = 'rombongs';
    protected $primaryKey = 'rombong_id';
    protected $fillable = [
        'user_id',
        'lapak_id',
        'nama_jualan',
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

    public function lapak()
    {
        return $this->belongsTo(Lapak::class, 'lapak_id', 'lapak_id');
    }

    public function waitingList()
    {
        return $this->hasMany(WaitingList::class, 'lapak_id', 'lapak_id')
            ->whereColumn('user_id', 'rombongs.user_id');
    }

    public function perpindahanLapaks()
    {
        return $this->hasMany(PerpindahanLapak::class, 'rombong_id', 'rombong_id');
    }
}
