<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lapak extends Model
{
    protected $table = 'lapaks';
    protected $primaryKey = 'lapak_id';

    protected $fillable = [
        'nama_lapak',
    ];

    public function rombongs()
    {
        return $this->hasMany(rombong::class, 'lapak_id', 'lapak_id')
            ->orderBy('urutan', 'asc');
    }

    public function waitingLists()
    {
        return $this->hasMany(WaitingList::class, 'lapak_id', 'lapak_id');
    }

    public function perpindahanLapaksAsal()
    {
        return $this->hasMany(PerpindahanLapak::class, 'lapak_asal_id', 'lapak_id');
    }
}
