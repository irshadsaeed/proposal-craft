<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'color'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    // Only count published posts
    public function publishedPosts()
    {
        return $this->hasMany(BlogPost::class, 'category_id')
                    ->where('status', 'published');
    }
}