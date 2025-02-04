<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class room_type extends Model
{
    use HasFactory;

    protected $table = 'room_type';
    protected $primaryKey = 'room_type_id';
    protected $fillable = [
        'room_type_id', 
        'room_type', 
        'base_price', 
        'max_occupancy', 
        'amenities', 
        'description', 
        'image', 
        'image2',
        'image3',
        'image4',
        'delete_status'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }
}
