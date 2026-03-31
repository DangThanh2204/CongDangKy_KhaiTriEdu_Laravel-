<?php

namespace App\Models;

use App\Support\StudyDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'instructor_id',
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
        'category_id',
        'status',
        'is_featured',
        'is_popular',
        'meta',
        'video_url',
        'pdf_path',
        'series_key',
        'learning_type',
        'announcement',
        'has_default_quiz',
        'default_quiz_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'rating' => 'decimal:1',
        'meta' => 'array',
        'has_default_quiz' => 'boolean',
        'default_quiz_data' => 'array',
    ];

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

    public function classes()
    {
        return $this->hasMany(CourseClass::class, 'course_id');
    }

    public function intakes()
    {
        return $this->classes();
    }

    public function resolveEnrollmentClass(): CourseClass
    {
        $courseClass = $this->classes()
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        if ($courseClass) {
            return $courseClass;
        }

        $fallbackInstructorId = $this->instructor_id
            ?: User::where('role', 'instructor')->value('id')
            ?: 1;

        return $this->classes()->create([
            'name' => $this->title,
            'instructor_id' => $fallbackInstructorId,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYears(5)->toDateString(),
            'schedule' => null,
            'meeting_info' => null,
            'max_students' => 0,
            'price_override' => null,
            'status' => 'active',
        ]);
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class)->orderBy('order')->orderBy('id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasManyThrough(CourseEnrollment::class, CourseClass::class, 'course_id', 'class_id');
    }

    public function certificates()
    {
        return $this->hasMany(CourseCertificate::class);
    }

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

    public function scopeDeliveryMode($query, ?string $mode)
    {
        if ($mode === 'online') {
            return $query->where(function ($innerQuery) {
                $innerQuery->where('learning_type', 'online')
                    ->orWhereNull('learning_type');
            });
        }

        if ($mode === 'offline') {
            return $query->whereIn('learning_type', ['offline', 'hybrid']);
        }

        return $query;
    }

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

    public function getDeliveryModeAttribute(): string
    {
        return ($this->learning_type ?? 'online') === 'online' ? 'online' : 'offline';
    }

    public function getDeliveryModeLabelAttribute(): string
    {
        return $this->delivery_mode === 'online' ? 'Online' : 'Offline';
    }

    public function isOnline(): bool
    {
        return $this->delivery_mode === 'online';
    }

    public function isOffline(): bool
    {
        return $this->delivery_mode === 'offline';
    }

    public function requiresManualApproval(): bool
    {
        return $this->isOffline();
    }

    public function updateRating()
    {
        $reviews = $this->reviews();
        $this->rating = $reviews->avg('rating') ?? 0;
        $this->total_rating = $reviews->count();
        $this->save();
    }

    public function getTotalDurationAttribute()
    {
        return $this->lessons()->sum('duration');
    }

    public function instructors()
    {
        if ($this->instructor) {
            return collect([$this->instructor]);
        }

        return User::whereIn('id', $this->classes()->pluck('instructor_id')->filter()->unique())->get();
    }

    public function getEstimatedDurationMinutesAttribute(): int
    {
        if (! $this->exists) {
            return max(0, (int) ($this->attributes['duration'] ?? 0));
        }

        $calculatedDuration = (int) $this->materials()
            ->get()
            ->sum(fn (CourseMaterial $material) => (int) $material->estimated_duration_minutes);

        if ($calculatedDuration > 0) {
            return $calculatedDuration;
        }

        return max(0, (int) ($this->getRawOriginal('duration') ?? 0));
    }

    public function getEstimatedDurationLabelAttribute(): string
    {
        return StudyDuration::formatMinutes($this->estimated_duration_minutes);
    }

    public function getDurationLabelAttribute(): string
    {
        return $this->estimated_duration_label;
    }

    public function syncStudyMetrics(): void
    {
        if (! $this->exists) {
            return;
        }

        $materials = $this->materials()->get();
        $duration = (int) $materials->sum(fn (CourseMaterial $material) => (int) $material->estimated_duration_minutes);
        $lessonsCount = $materials->count();

        $currentDuration = max(0, (int) ($this->getRawOriginal('duration') ?? 0));
        $currentLessonsCount = max(0, (int) ($this->getRawOriginal('lessons_count') ?? 0));

        if ($currentDuration === $duration && $currentLessonsCount === $lessonsCount) {
            return;
        }

        $this->forceFill([
            'duration' => $duration,
            'lessons_count' => $lessonsCount,
        ])->saveQuietly();
    }
}