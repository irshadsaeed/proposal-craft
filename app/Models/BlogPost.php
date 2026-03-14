<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'content_html',
        'cover_image', 'status', 'is_featured', 'is_template',
        'read_time', 'views_count', 'meta_description', 'seo_title',
        'published_at', 'author_id', 'category_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
        'is_template'  => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────

    // Author is an AdminUser (admin writes blog posts)
    public function author()
    {
        return $this->belongsTo(AdminUser::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopePublished($q)
    {
        return $q->where('status', 'published')
                 ->where('published_at', '<=', now());
    }

    // ── Auto read time ────────────────────────────────────────

    protected static function booted()
    {
        static::saving(function ($post) {
            $words = str_word_count(strip_tags($post->content ?? ''));
            $post->read_time = max(1, (int) ceil($words / 200));
        });
    }
}