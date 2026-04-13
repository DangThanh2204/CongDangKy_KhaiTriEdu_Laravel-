<?php
// [file name]: PostCategory.php
// [file content begin]
namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'order',
        'is_active'
    ];

    protected $appends = [
        'posts_count'
    ];

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    // Accessors
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    // Thêm vào PostCategory.php
    public function getActivePostsCountAttribute()
    {
        return $this->posts()->where('status', 'published')->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

}
// [file content end]
