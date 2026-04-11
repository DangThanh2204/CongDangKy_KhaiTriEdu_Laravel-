<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'color',
        'order',
        'is_active'
    ];

    protected $appends = [
        'courses_count'
    ];

    // Relationships
    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(CourseCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CourseCategory::class, 'parent_id');
    }

    // Accessors
    public function getCoursesCountAttribute()
    {
        return $this->courses()->count();
    }

    public function getActiveCoursesCountAttribute()
    {
        return $this->courses()->where('status', 'published')->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function($q) {
            $q->active()->ordered();
        }]);
    }
}