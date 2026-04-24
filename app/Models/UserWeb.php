<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeb extends Model
{
    use HasFactory;

    protected $table = 'users_web'; 

    protected $primaryKey = 'id';

    protected $fillable = [
        'supabase_user_id',
        'name',
        'email',
        'last_login',
    ];

    // otomatis cast last_login ke datetime
    protected $casts = [
        'last_login' => 'datetime',
    ];

    // Hubungan ke statistik nutrisi
    public function nutrisiStatistics()
    {
        return $this->hasMany(NutriStatistic::class, 'user_id', 'id');
    }
}