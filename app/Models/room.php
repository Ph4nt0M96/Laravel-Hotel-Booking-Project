<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room extends Model
{
    use HasFactory;

    protected $table = 'room';
    protected $primaryKey = 'room_id';

    protected $fillable = [
        'room_id',
        'room_type_id',
        'view_id',
        'floor',
        'view_id',
        'is_held',
        'is_available',
    ];

    public function roomType()
    {
        return $this->belongsTo(Room_Type::class, 'room_type_id');
    }

    public function view()
    {
        return $this->belongsTo(View::class, 'view_id');
    }

    public function bookingDetails()
    {
        return $this->hasMany(Booking_Detail::class, 'room_id');
    }
}
