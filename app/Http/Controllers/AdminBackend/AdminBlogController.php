<?php

namespace App\Http\Controllers\AdminBackend;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    /* ═══════════════════════════════════════════════════════════
       INDEX  —  posts list
    ═══════════════════════════════════════════════════════════ */

    public function index(Request $request)
    {
        $query = BlogPost::with(['category'])->latest('published_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($category = $request->get('category')) {
            // BUG FIX: blade/controller used 'blog_category_id' but column is 'category_id'
            $query->where('category_id', $category);
        }

        $posts      = $query->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();
        $totalPosts = BlogPost::count();
        $published  = BlogPost::where('status', 'published')->count();
        $drafts     = BlogPost::where('status', 'draft')->count();

        return view('admin-dashboard.blog-view', compact(
            'posts',
            'categories',
            'totalPosts',
            'published',
            'drafts'
        ));
    }


    /* ═══════════════════════════════════════════════════════════
       SHOW  —  post detail (read-only view)
    ═══════════════════════════════════════════════════════════ */

    public function show(BlogPost $post)
{
    $post->load('tags', 'category', 'author');
    $categories     = BlogCategory::orderBy('name')->get();
    $relatedPosts   = BlogPost::where('category_id', $post->category_id)
                        ->where('id', '!=', $post->id)
                        ->latest()
                        ->limit(5)
                        ->get();

    return view('admin-dashboard.blog-detail', compact('post', 'categories', 'relatedPosts'));
}

    /* ═══════════════════════════════════════════════════════════
       CREATE  —  new post form
    ═══════════════════════════════════════════════════════════ */

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags       = BlogTag::orderBy('name')->get();

        return view('admin-dashboard.blog-add', compact('categories', 'tags'));
    }


    /* ═══════════════════════════════════════════════════════════
       STORE  —  save new post
    ═══════════════════════════════════════════════════════════ */

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeCoverImage($request);
        }

        $data['author_id'] = auth('admin')->id();
        $data['slug']      = !empty($data['slug']) ? $data['slug'] : Str::slug($data['title']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        if ($request->filled('tags')) {
            $post->tags()->sync($this->syncTags($request->tags));
        }

        AdminActivityLog::log('blog.created', $post);

        return redirect()
            ->route('admin.blog.index')
            ->with('flash', 'success')
            ->with('flash_msg', "\"{$post->title}\" published successfully.");
    }


    /* ═══════════════════════════════════════════════════════════
       EDIT  —  edit form
    ═══════════════════════════════════════════════════════════ */

    public function edit(BlogPost $post)
    {
        $post->load('tags', 'category');
        $categories = BlogCategory::orderBy('name')->get();
        $tags       = BlogTag::orderBy('name')->get();

        return view('admin-dashboard.blog-edit', compact('post', 'categories', 'tags'));
    }


    /* ═══════════════════════════════════════════════════════════
       UPDATE  —  save edits
    ═══════════════════════════════════════════════════════════ */

    public function update(Request $request, BlogPost $post)
    {
        $data = $this->validatePost($request, $post);

        // Cover image — new upload
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeCoverImage($request);
        }

        // Cover image — explicit remove
        if ($request->input('remove_cover') === '1') {
            $data['cover_image'] = null;
        }

        // Auto-set published_at on first publish
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        if ($request->filled('tags')) {
            $post->tags()->sync($this->syncTags($request->tags));
        } else {
            $post->tags()->detach();
        }

        AdminActivityLog::log('blog.updated', $post);

        return redirect()
            ->route('admin.blog.index')
            ->with('flash', 'success')
            ->with('flash_msg', "\"{$post->title}\" updated.");
    }


    /* ═══════════════════════════════════════════════════════════
       DESTROY  —  delete post (AJAX)
    ═══════════════════════════════════════════════════════════ */

    public function destroy(BlogPost $post)
    {
        $title = $post->title;
        AdminActivityLog::log('blog.deleted', $post);
        $post->delete();

        return response()->json([
            'ok'      => true,
            'message' => "\"{$title}\" deleted.",
        ]);
    }


    /* ═══════════════════════════════════════════════════════════
       PRIVATE HELPERS
    ═══════════════════════════════════════════════════════════ */

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        $slugRule = $post
            ? 'nullable|string|max:220|unique:blog_posts,slug,' . $post->id
            : 'nullable|string|max:220|unique:blog_posts,slug';

        return $request->validate([
            // ── Core fields ───────────────────────────────────────────
            'title'            => 'required|string|max:200',
            'slug'             => $slugRule,
            'excerpt'          => 'nullable|string|max:400',
            'content'          => 'required|string',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',

            // ── Cover image ───────────────────────────────────────────
            // BUG FIX: original had max:2048 (KB) — raised to 4096 to match
            // the blade hint "Max 4 MB". Also added webp to mimes.
            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',

            'remove_cover'     => 'nullable|in:0,1',

            // ── Organisation ──────────────────────────────────────────
            // BUG FIX: blade sends 'category_id', old controller validated
            // 'blog_category_id' — mismatched field was silently ignored,
            // category was never saved.
            'category_id'      => 'nullable|exists:blog_categories,id',
            'tags'             => 'nullable|string',   // comma-separated from JS
            'read_time'        => 'nullable|integer|min:1|max:120',

            // ── Publishing ────────────────────────────────────────────
            'is_featured'      => 'boolean',
            'allow_comments'   => 'boolean',

            // ── SEO / Meta ────────────────────────────────────────────
            // BUG FIX: old controller validated 'seo_title' but blade sends
            // 'meta_title'. Corrected to match blade input name.
            'meta_title'       => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'og_image'         => 'nullable|url|max:500',
        ]);
    }

    private function storeCoverImage(Request $request): string
    {
        $file     = $request->file('cover_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('admin-blog-posts/blog-post-pic', $filename, 'public');

        return '/storage/' . $path;
    }

    private function syncTags(string|array $tags): array
    {
        $tags = is_string($tags) ? explode(',', $tags) : (array) $tags;
        $ids  = [];

        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) continue;
            $slug  = Str::slug($tag);
            $ids[] = BlogTag::firstOrCreate(['slug' => $slug], ['name' => $tag])->id;
        }

        return $ids;
    }
}