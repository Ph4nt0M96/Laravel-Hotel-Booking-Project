<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking_extraservice extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'service_id',
        'quantity',
    ];

    protected $table = 'booking_extraservice';

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function extraService()
    {
        return $this->belongsTo(Extra_Service::class, 'service_id');
    }
}
