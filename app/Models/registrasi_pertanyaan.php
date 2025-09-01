<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class registrasi_pertanyaan extends Model
{
    protected $table = 'registrasi_pertanyaans';
    protected $primariKey = 'registrasi_pertanyaan_id';
    
    protected $fillable = [
        'user_id',
        'pertanyaan1',
        'pertanyaan1_custom',
        'pertanyaan2',
        'pertanyaan2_custom'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
}
