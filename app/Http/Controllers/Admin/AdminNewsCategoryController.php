<?php
// [file name]: AdminNewsCategoryController.php
// [file content begin]
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminNewsCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $categories = PostCategory::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($status !== null, function ($query) use ($status) {
                if ($status === 'active') {
                    return $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    return $query->where('is_active', false);
                }
                return $query;
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Thống kê - SỬA LẠI PHẦN NÀY
        $stats = [
            'totalCategories' => PostCategory::count(),
            'activeCategories' => PostCategory::where('is_active', true)->count(),
            'inactiveCategories' => PostCategory::where('is_active', false)->count(),
        ];

        return view('admin.news-categories.index', compact(
            'categories',
            'stats'
        ));
    }

    public function create()
    {
        return view('admin.news-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:post_categories',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|size:7', // HEX color
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        PostCategory::create([
            'name' => $request->name,
            'slug' => $this->generateUniqueSlug($request->name),
            'description' => $request->description,
            'color' => $request->color,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.news-categories.index')
            ->with('success', 'Danh mục đã được tạo thành công!');
    }

    public function edit(PostCategory $category)
    {
        return view('admin.news-categories.edit', compact('category'));
    }

    public function update(Request $request, PostCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:post_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|size:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ];

        // Update slug if name changed
        if ($category->name !== $request->name) {
            $data['slug'] = $this->generateUniqueSlug($request->name, $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.news-categories.index')
            ->with('success', 'Danh mục đã được cập nhật thành công!');
    }

    public function destroy(PostCategory $category)
    {
        // Check if category has posts
        if ($category->posts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Không thể xóa danh mục đang chứa bài viết!');
        }

        $category->delete();

        return redirect()->route('admin.news-categories.index')
            ->with('success', 'Danh mục đã được xóa thành công!');
    }

    public function toggleStatus(PostCategory $category)
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);

        $status = $category->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        
        return redirect()->back()
            ->with('success', "Đã {$status} danh mục {$category->name}!");
    }

    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (PostCategory::where('slug', $slug)
            ->when($excludeId, function($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
// [file content end]