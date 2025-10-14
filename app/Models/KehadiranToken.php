<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class KehadiranToken extends Model
{
    protected $fillable = ['user_id', 'token', 'tanggal', 'expired_at', 'is_used'];

    protected $casts = [
        'tanggal' => 'date',
        'expired_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    public static function generateToken($userId, $tanggal = null)
    {
        $tanggal = $tanggal ?? now()->format('Y-m-d');
        
        // Invalidate any existing tokens for this user and date
        static::where('user_id', $userId)
            ->where('tanggal', $tanggal)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate new token
        return static::create([
            'user_id' => $userId,
            'token' => bin2hex(random_bytes(32)),
            'tanggal' => $tanggal,
            'expired_at' => now()->addHours(24),
            'is_used' => false
        ]);
    }

    public static function validateToken($token)
    {
        return static::where('token', $token)
            ->where('is_used', false)
            ->where('expired_at', '>', now())
            ->first();
    }

    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}