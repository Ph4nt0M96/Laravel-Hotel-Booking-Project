<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class guest extends Model
{
    use HasFactory;

    protected $table = 'guest';
    protected $primaryKey = 'guest_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'guest_id');
    }

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone_number',
        'nrc_no',
        'uid',
    ];
}
