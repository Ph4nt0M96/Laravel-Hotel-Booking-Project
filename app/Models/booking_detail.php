<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking_detail extends Model
{
    use HasFactory;

    protected $table = 'booking_detail';
    protected $primaryKey = 'detail_id';
    protected $fillable = [
        'booking_id', 'room_id', 'check_in_date', 'check_out_date', 'total_cost',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    
    public function extraServices()
    {
        // return $this->belongsToMany(extra_service::class, 'booking_extraservice', 'detail_id', 'service_id')
        //             ->withPivot('quantity');
        return $this->hasMany(Booking_ExtraService::class, 'detail_id');
    }
}
