<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $q = SystemLog::with('user');

        if ($request->filled('user')) {
            $term = $request->get('user');
            $q->whereHas('user', fn($q2) => $q2->where('fullname', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }

        if ($request->filled('category')) {
            $q->where('category', $request->get('category'));
        }

        if ($request->filled('action')) {
            $q->where('action', 'like', "%{$request->get('action')}%");
        }

        if ($request->filled('reference')) {
            $q->where('reference', 'like', "%{$request->get('reference')}%");
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->get('to'));
        }

        $logs = $q->latest()->paginate(25)->withQueryString();

        return view('admin.system-logs.index', ['logs' => $logs]);
    }

    public function export(Request $request)
    {
        $q = SystemLog::with('user');

        // apply same filters as index
        if ($request->filled('user')) {
            $term = $request->get('user');
            $q->whereHas('user', fn($q2) => $q2->where('fullname', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }
        if ($request->filled('category')) $q->where('category', $request->get('category'));
        if ($request->filled('action')) $q->where('action', 'like', "%{$request->get('action')}%");
        if ($request->filled('reference')) $q->where('reference', 'like', "%{$request->get('reference')}%");
        if ($request->filled('from')) $q->whereDate('created_at', '>=', $request->get('from'));
        if ($request->filled('to')) $q->whereDate('created_at', '<=', $request->get('to'));

        $fileName = 'system-logs-' . now()->format('Ymd-His') . '.csv';

        $response = new StreamedResponse(function () use ($q) {
            $handle = fopen('php://output', 'w');
            // header
            fputcsv($handle, ['id','created_at','category','action','user_id','user_fullname','reference','ip','user_agent','details']);

            $q->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->created_at->toDateTimeString(),
                        $row->category,
                        $row->action,
                        $row->user_id,
                        $row->user?->fullname,
                        $row->reference,
                        $row->ip,
                        $row->user_agent,
                        is_array($row->details) ? json_encode($row->details, JSON_UNESCAPED_UNICODE) : $row->details,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
