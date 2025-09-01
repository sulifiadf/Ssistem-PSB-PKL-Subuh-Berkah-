<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class keuangan extends Model
{
    protected $table = 'keuangans';
    protected $primaryKey = 'keuangan_id';
    protected $fillable = [
        'tanggal',
        'jenis',
        'jumlah',
        'keterangan'
    ];

}
