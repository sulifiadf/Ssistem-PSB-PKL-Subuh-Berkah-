<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class waiting_list extends Model
{
    protected $table = 'waiting_lists';
    protected $primaryKey = 'waiting_list_id';
    protected $fillable = [
        'user_id',
        'rombong_id',
        'tanggal pengajuan',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
