<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class registrasi_pertanyaan extends Model
{
    protected $table = 'registrasi_pertanyaans';
    protected $primaryKey = 'registrasi_pertanyaan_id'; // PERBAIKAN: typo "primari" -> "primary"
    
    protected $fillable = [
        'user_id',
        'pertanyaan1',
        'pertanyaan1_custom',
        'pertanyaan2',
        'pertanyaan2_custom',
        'mulai_jual',
        'penjaga_stand'
    ];

    // Timestamps (created_at, updated_at) - pastikan ada di tabel
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}