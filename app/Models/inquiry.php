<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inquiry extends Model
{
    use HasFactory;

    protected $table = 'inquiry';
    protected $primaryKey = 'inquiry_id';

    protected $fillable = ['guest_id', 'message', 'sentdate',];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }
}
