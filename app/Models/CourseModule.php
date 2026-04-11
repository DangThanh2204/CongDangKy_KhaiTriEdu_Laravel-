<?php

namespace App\Models;

use App\Support\StudyDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class, 'course_module_id')->orderBy('order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    public function getEstimatedDurationMinutesAttribute(): int
    {
        $materials = $this->relationLoaded('materials')
            ? $this->materials
            : $this->materials()->get();

        return (int) $materials->sum(fn (CourseMaterial $material) => (int) $material->estimated_duration_minutes);
    }

    public function getEstimatedDurationLabelAttribute(): string
    {
        return StudyDuration::formatMinutes($this->estimated_duration_minutes);
    }
}