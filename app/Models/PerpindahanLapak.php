<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerpindahanLapak extends Model
{
    protected $table = 'perpindahan_lapaks';
    protected $primaryKey = 'perpindahan_lapak_id';
    protected $fillable = [
        'lapak_asal_id',
        'lapak_tujuan_id',
        'rombong_id',
        'tanggal_perpindahan',
    ];

    public function lapakAsal()
    {
        return $this->belongsTo(Lapak::class, 'lapak_asal_id', 'lapak_id');
    }

    public function lapakTujuan()
    {
        return $this->belongsTo(Lapak::class, 'lapak_tujuan_id', 'lapak_id');
    }

    public function rombong()
    {
        return $this->belongsTo(Rombong::class, 'rombong_id', 'rombong_id');
    }
}
