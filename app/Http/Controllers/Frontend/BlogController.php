<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogPost;
use App\Models\BlogCategory;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::published()
            ->with(['author', 'category'])
            ->when($request->category, fn($q) => $q->whereHas('category', fn($q) => $q->where('slug', $request->category)))
            ->when($request->tag, fn($q) => $q->whereHas('tags', fn($q) => $q->where('slug', $request->tag)))
            ->latest('published_at')
            ->paginate(9);

        $featured   = BlogPost::published()->where('is_featured', true)->with(['author', 'category'])->latest('published_at')->first();
        $categories = BlogCategory::withCount('publishedPosts')->having('published_posts_count', '>', 0)->get();

        return view('frontend.pages.blog-index', compact('posts', 'featured', 'categories'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->with(['author', 'category', 'tags'])->firstOrFail();

        $post->increment('views_count');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with(['author', 'category'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('frontend.pages.blog-post', compact('post', 'related'));
    }

    public function search(Request $request)
    {
        $q = $request->string('q')->trim();

        abort_if($q->isEmpty(), 400);

        $posts = BlogPost::published()
            ->with(['author', 'category'])
            ->where(
                fn($query) => $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%")
            )
            ->latest('published_at')
            ->paginate(9)
            ->appends(['q' => $q]);

        $categories = BlogCategory::withCount('posts')->having('posts_count', '>', 0)->get();
        $featured   = null; // no featured on search results

        return view('frontend.pages.blog-index', compact('posts', 'featured', 'categories'));
    }

    public function suggestions(Request $request)
    {
        $q = $request->string('q')->trim();

        if ($q->isEmpty() || $q->length() < 2) {
            return response()->json([]);
        }

        $suggestions = BlogPost::published()
            ->select('title', 'slug', 'read_time')
            ->with('category:id,name,color')
            ->where(
                fn($query) => $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%")
            )
            ->latest('published_at')
            ->take(5)
            ->get();

        return response()->json($suggestions);
    }
}
