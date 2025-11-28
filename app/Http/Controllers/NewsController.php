<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
{
    $query = Post::published()->with(['author', 'category']);

    // search
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhere('excerpt', 'like', "%{$request->search}%")
              ->orWhere('content', 'like', "%{$request->search}%");
        });
    }

    // sort
    $sort = $request->get('sort', 'latest');
    switch ($sort) {
        case 'popular':
            $query->orderBy('view_count', 'desc');
            break;
        case 'featured':
            $query->featured()->orderBy('published_at', 'desc');
            break;
        default:
            $query->orderBy('published_at', 'desc');
    }

    // paginate
    $posts = $query->paginate(12);

    // categories chỉ đếm bài đã publish
    $categories = PostCategory::active()
        ->ordered()
        ->withCount(['posts as posts_count' => function ($q) {
            $q->published();
        }])
        ->get();

    // featured
    $featuredPosts = Post::published()->featured()->latest()->take(3)->get();

    // POPULAR POSTS (không có cái này là lỗi)
    $popularPosts = Post::published()
        ->orderBy('view_count', 'desc')
        ->take(5)
        ->get();

    return view('news.index', compact(
        'posts',
        'categories',
        'featuredPosts',
        'sort',
        'popularPosts'
    ));
}


    public function show($slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['author', 'category'])
            ->firstOrFail();
        
        // Increment view count
        $post->incrementViewCount();
        
        // Related posts
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with('author')
            ->latest()
            ->take(4)
            ->get();
        
        return view('news.show', compact('post', 'relatedPosts'));
    }

    public function category($slug)
    {
        $category = PostCategory::where('slug', $slug)->active()->firstOrFail();
        $posts = Post::published()
            ->byCategory($slug)
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate(12);
        
        $categories = PostCategory::active()
    ->ordered()
    ->withCount(['posts as posts_count' => function ($q) {
        $q->published();
    }])
    ->get();

        
        return view('news.category', compact('category', 'posts', 'categories'));
    }
}