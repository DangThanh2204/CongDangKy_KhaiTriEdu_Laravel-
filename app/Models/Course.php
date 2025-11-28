<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'thumbnail',
        'banner_image',
        'level',
        'duration',
        'lessons_count',
        'students_count',
        'rating',
        'total_rating',
        'instructor_id',
        'category_id',
        'status',
        'is_featured',
        'is_popular',
        'meta'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'rating' => 'decimal:1',
        'meta' => 'array'
    ];

    // Relationships
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class);
    }

    public function lessons()
    {
        return $this->hasManyThrough(CourseLesson::class, CourseSection::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    // Accessors
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return asset('images/default-course.jpg');
    }

    public function getBannerImageUrlAttribute()
    {
        if ($this->banner_image) {
            return asset('storage/' . $this->banner_image);
        }
        return asset('images/default-banner.jpg');
    }

    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?: $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    // Methods
    public function updateRating()
    {
        $reviews = $this->reviews();
        $this->rating = $reviews->avg('rating') ?? 0;
        $this->total_rating = $reviews->count();
        $this->save();
    }

    public function isEnrolled($userId = null)
    {
        $userId = $userId ?: auth()->id();
        return $this->enrollments()->where('user_id', $userId)->exists();
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('duration');
    }
}