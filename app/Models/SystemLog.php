<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'user_id', 'category', 'action', 'details', 'reference', 'ip', 'user_agent'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCategory($q, $category)
    {
        return $q->when($category, fn($q) => $q->where('category', $category));
    }

    public function scopeAction($q, $action)
    {
        return $q->when($action, fn($q) => $q->where('action', $action));
    }

    public function getPlatformAttribute()
    {
        $ua = $this->user_agent ?? '';
        $uaLower = strtolower($ua);

        if (strpos($uaLower, 'android') !== false) return 'Android';
        if (strpos($uaLower, 'iphone') !== false || strpos($uaLower, 'ipad') !== false || strpos($uaLower, 'ipod') !== false) return 'iOS';
        if (strpos($uaLower, 'macintosh') !== false || strpos($uaLower, 'mac os x') !== false) return 'macOS';
        if (strpos($uaLower, 'windows') !== false) return 'Windows';
        if (strpos($uaLower, 'linux') !== false) return 'Linux';

        return 'Unknown';
    }

    public function getDeviceAttribute()
    {
        $ua = $this->user_agent ?? '';
        $uaLower = strtolower($ua);

        if (strpos($uaLower, 'mobile') !== false || strpos($uaLower, 'iphone') !== false || strpos($uaLower, 'android') !== false && strpos($uaLower, 'tablet') === false) {
            // mobile phone
            if (strpos($uaLower, 'iphone') !== false) return 'iPhone';
            if (strpos($uaLower, 'android') !== false) {
                // try to detect tablet vs phone
                if (strpos($uaLower, 'tablet') !== false || strpos($uaLower, 'nexus 7') !== false || strpos($uaLower, 'sm-t') !== false) return 'Android Tablet';
                return 'Android Phone';
            }
            return 'Mobile';
        }

        if (strpos($uaLower, 'ipad') !== false || strpos($uaLower, 'tablet') !== false) return 'Tablet';

        return 'Desktop';
    }

    public function getBrowserAttribute()
    {
        $ua = $this->user_agent ?? '';
        $uaLower = strtolower($ua);

        if (strpos($uaLower, 'edg/') !== false || strpos($uaLower, 'edge') !== false) return 'Edge';
        if (strpos($uaLower, 'opr/') !== false || strpos($uaLower, 'opera') !== false) return 'Opera';
        if (strpos($uaLower, 'chrome/') !== false && strpos($uaLower, 'chromium') === false && strpos($uaLower, 'edg/') === false && strpos($uaLower, 'opr/') === false) return 'Chrome';
        if (strpos($uaLower, 'firefox') !== false) return 'Firefox';
        if (strpos($uaLower, 'safari') !== false && strpos($uaLower, 'chrome') === false) return 'Safari';

        return 'Unknown';
    }
}
