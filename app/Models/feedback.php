<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';
    protected $fillable = ['guest_id', 'comment', 'rating', 'status'];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }
}
