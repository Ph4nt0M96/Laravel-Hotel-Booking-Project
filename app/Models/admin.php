<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class admin extends Model
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'admin_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'staff_id');
    }
}
