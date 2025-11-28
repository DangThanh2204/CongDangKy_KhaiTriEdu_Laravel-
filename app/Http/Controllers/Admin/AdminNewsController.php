<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category']);
        
        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        $posts = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = PostCategory::active()->get();
        
        // Add statistics variables
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();
        $draftPosts = Post::where('status', 'draft')->count();
        $totalViews = Post::sum('view_count');
        
        return view('admin.news.index', compact(
            'posts', 
            'categories', 
            'totalPosts',
            'publishedPosts', 
            'draftPosts', 
            'totalViews'
        ));
    }

    public function create()
    {
        $categories = PostCategory::active()->get();
        return view('admin.news.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:post_categories,id',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->only([
            'title', 'excerpt', 'content', 'category_id', 'status'
        ]);

        // Generate slug
        $data['slug'] = $this->generateUniqueSlug($request->title);
        $data['author_id'] = auth()->id();
        
        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }
        
        // Published at
        if ($request->status === 'published' && !$request->published_at) {
            $data['published_at'] = now();
        } elseif ($request->published_at) {
            $data['published_at'] = $request->published_at;
        }
        
        // Meta data
        $data['meta'] = [
            'title' => $request->meta_title,
            'description' => $request->meta_description,
            'is_featured' => $request->has('is_featured'),
        ];

        Post::create($data);

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    public function edit(Post $news)
    {
        $categories = PostCategory::active()->get();
        return view('admin.news.edit', compact('news', 'categories'));
    }
	// Thêm phương thức preview vào controller
	public function preview(Post $news)
	{
		// Kiểm tra quyền xem trước nếu cần
		if (!auth()->user()->can('view', $news) && $news->status !== 'published') {
			abort(403, 'Bạn không có quyền xem trước bài viết này.');
		}
		
		return view('admin.news.preview', compact('news'));
	}

    public function update(Request $request, Post $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:post_categories,id',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->only([
            'title', 'excerpt', 'content', 'category_id', 'status'
        ]);

        // Update slug if title changed
        if ($news->title !== $request->title) {
            $data['slug'] = $this->generateUniqueSlug($request->title, $news->id);
        }
        
        // Handle featured image
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }
        
        // Published at
        if ($request->status === 'published' && !$news->published_at) {
            $data['published_at'] = now();
        } elseif ($request->published_at) {
            $data['published_at'] = $request->published_at;
        }
        
        // Meta data
        $data['meta'] = [
            'title' => $request->meta_title,
            'description' => $request->meta_description,
            'is_featured' => $request->has('is_featured'),
        ];

        $news->update($data);

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được cập nhật thành công!');
    }

    public function destroy(Post $news)
    {
        // Delete featured image
        if ($news->featured_image) {
            Storage::disk('public')->delete($news->featured_image);
        }
        
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được xóa thành công!');
    }

    public function toggleFeatured(Post $news)
    {
        $meta = $news->meta ?? [];
        $meta['is_featured'] = !($meta['is_featured'] ?? false);
        
        $news->update(['meta' => $meta]);
        
        $status = $meta['is_featured'] ? 'nổi bật' : 'bỏ nổi bật';
        return back()->with('success', "Đã {$status} bài viết!");
    }

    private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Post::where('slug', $slug)
            ->when($excludeId, function($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}