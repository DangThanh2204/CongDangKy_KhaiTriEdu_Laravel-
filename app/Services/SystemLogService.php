<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SystemLogService
{
    /**
     * Record a system log entry.
     *
     * @param string $category ('security'|'transaction'|'system')
     * @param string $action
     * @param array|string|null $details
     * @param string|null $reference
     * @param \Illuminate\Http\Request|null $request
     * @return SystemLog
     */
    public static function record(string $category, string $action, $details = null, ?string $reference = null, ?Request $request = null)
    {
        $user = Auth::user();

        $ip = null; $ua = null;
        if ($request) {
            $ip = $request->ip();
            $ua = $request->userAgent();
        } elseif (php_sapi_name() !== 'cli') {
            $ip = request()->ip();
            $ua = request()->userAgent();
        }

        $detailsToStore = is_array($details) ? $details : (is_null($details) ? null : ['message' => (string)$details]);

        return SystemLog::create([
            'user_id' => $user?->id,
            'category' => $category,
            'action' => $action,
            'details' => $detailsToStore,
            'reference' => $reference,
            'ip' => $ip,
            'user_agent' => $ua,
        ]);
    }
}
