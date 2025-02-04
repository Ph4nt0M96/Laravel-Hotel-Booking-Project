<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class extra_service extends Model
{
    use HasFactory;

    protected $table = 'extra_service';
    protected $primaryKey = 'service_id';
    protected $fillable = [
        'service_name',
        'service_price',
        'description',
        'image',
    ];

    public function bookingExtraServices()
    {
        return $this->hasMany(Booking_ExtraService::class, 'service_id');
    }
}
