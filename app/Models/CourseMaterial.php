<?php

namespace App\Models;

use App\Support\StudyDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;
use Illuminate\Support\Carbon;

class CourseMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'course_module_id',
        'type',
        'title',
        'content',
        'file_path',
        'metadata',
        'estimated_duration_minutes',
        'order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'estimated_duration_minutes' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function courseModule()
    {
        return $this->module();
    }

    public function progresses()
    {
        return $this->hasMany(CourseMaterialProgress::class, 'course_material_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(CourseMaterialQuizAttempt::class, 'course_material_id')->latest('completed_at');
    }

    public function requiresQuizPass(): bool
    {
        return $this->type === 'quiz';
    }

    public function isMeeting(): bool
    {
        return $this->type === 'meeting';
    }

    public function getQuizQuestionsAttribute(): array
    {
        return collect($this->metadata['questions'] ?? [])
            ->filter(fn ($question) => filled($question['question'] ?? null))
            ->values()
            ->all();
    }

    public function getEstimatedDurationMinutesAttribute($value): int
    {
        return is_null($value)
            ? StudyDuration::estimateForPersistedMaterial($this)
            : (int) $value;
    }

    public function getEstimatedDurationLabelAttribute(): string
    {
        return StudyDuration::formatMinutes($this->estimated_duration_minutes);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'video' => 'Video YouTube',
            'pdf' => 'Tài liệu PDF / Word',
            'assignment' => 'Bài tập',
            'quiz' => 'Quiz',
            'meeting' => 'Buổi học Meet',
            default => ucfirst((string) $this->type),
        };
    }

    public function getExternalUrlAttribute(): ?string
    {
        return match ($this->type) {
            'video' => data_get($this->metadata, 'url'),
            'meeting' => data_get($this->metadata, 'meeting_url'),
            default => null,
        };
    }

    public function getDocumentOriginalNameAttribute(): ?string
    {
        return data_get($this->metadata, 'document_original_name');
    }

    public function getMeetingUrlAttribute(): ?string
    {
        $url = data_get($this->metadata, 'meeting_url');

        return filled($url) ? (string) $url : null;
    }

    public function getMeetingStartsAtAttribute(): ?Carbon
    {
        return $this->parseMetadataDate('meeting_starts_at');
    }

    public function getMeetingEndsAtAttribute(): ?Carbon
    {
        return $this->parseMetadataDate('meeting_ends_at');
    }

    public function getMeetingNoteAttribute(): ?string
    {
        $note = data_get($this->metadata, 'meeting_note');

        return filled($note) ? (string) $note : null;
    }

    public function getMeetingStatusAttribute(): string
    {
        if (! $this->isMeeting()) {
            return 'default';
        }

        $startsAt = $this->meeting_starts_at;
        $endsAt = $this->meeting_ends_at;
        $now = now();

        if ($startsAt && $now->lt($startsAt)) {
            return 'upcoming';
        }

        if ($endsAt && $now->gt($endsAt)) {
            return 'ended';
        }

        if ($startsAt) {
            return 'live';
        }

        return 'available';
    }

    public function getMeetingStatusLabelAttribute(): string
    {
        return match ($this->meeting_status) {
            'upcoming' => 'Chưa tới giờ mở',
            'live' => 'Đang mở phòng học',
            'ended' => 'Buổi học đã kết thúc',
            'available' => 'Mở link học',
            default => 'Nội dung học',
        };
    }

    public function getMeetingStatusBadgeClassAttribute(): string
    {
        return match ($this->meeting_status) {
            'upcoming' => 'bg-warning text-dark',
            'live' => 'bg-success',
            'ended' => 'bg-secondary',
            'available' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    public function getMeetingWindowLabelAttribute(): ?string
    {
        if (! $this->isMeeting()) {
            return null;
        }

        $startsAt = $this->meeting_starts_at;
        $endsAt = $this->meeting_ends_at;

        if (! $startsAt && ! $endsAt) {
            return null;
        }

        if ($startsAt && $endsAt) {
            if ($startsAt->isSameDay($endsAt)) {
                return $startsAt->format('d/m/Y H:i') . ' - ' . $endsAt->format('H:i');
            }

            return $startsAt->format('d/m/Y H:i') . ' - ' . $endsAt->format('d/m/Y H:i');
        }

        if ($startsAt) {
            return 'Mở từ ' . $startsAt->format('d/m/Y H:i');
        }

        return 'Kết thúc lúc ' . $endsAt->format('d/m/Y H:i');
    }

    public function canJoinMeeting(): bool
    {
        if (! $this->isMeeting() || blank($this->meeting_url)) {
            return false;
        }

        $startsAt = $this->meeting_starts_at;

        return ! $startsAt || now()->gte($startsAt);
    }

    public function canComplete(): bool
    {
        if (! $this->isMeeting()) {
            return true;
        }

        $startsAt = $this->meeting_starts_at;

        return ! $startsAt || now()->gte($startsAt);
    }

    public function getDocumentExtensionAttribute(): ?string
    {
        $extension = strtolower((string) data_get($this->metadata, 'document_extension', ''));

        if ($extension !== '') {
            return $extension;
        }

        if (! $this->file_path) {
            return null;
        }

        return strtolower((string) pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    public function getDocumentActionLabelAttribute(): string
    {
        return match ($this->document_extension) {
            'doc', 'docx' => 'Mở tài liệu Word',
            default => 'Mở tài liệu',
        };
    }

    public function getDocumentIconClassAttribute(): string
    {
        return match ($this->document_extension) {
            'doc', 'docx' => 'fas fa-file-word',
            default => 'fas fa-file-pdf',
        };
    }

    protected function parseMetadataDate(string $key): ?Carbon
    {
        $value = data_get($this->metadata, $key);

        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
