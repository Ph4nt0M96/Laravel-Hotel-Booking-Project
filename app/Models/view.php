<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class view extends Model
{
    use HasFactory;

    protected $table = 'view';
    protected $primaryKey = 'view_id';

    protected $fillable = [
        'view_name',
        'description',
        'image',
        'delete_status',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'view_id');
    }
}
