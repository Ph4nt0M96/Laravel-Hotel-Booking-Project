<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
    use HasFactory;

    protected $table = 'booking';
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'guest_id', 'admin_id', 'confirm_until', 'booking_status',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function bookingDetails()
    {
        return $this->hasMany(Booking_Detail::class, 'booking_id');
        
    }
}
