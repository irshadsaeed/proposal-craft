<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'category', 'description', 'color', 'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}