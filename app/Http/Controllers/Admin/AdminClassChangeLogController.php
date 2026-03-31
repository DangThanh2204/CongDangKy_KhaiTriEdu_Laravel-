<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassChangeLog;
use App\Models\CourseClass;

class AdminClassChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassChangeLog::with(['enrollment.user', 'user'])->orderBy('created_at', 'desc');

        $logs = $query->paginate(20);

        // collect class ids to display names
        $classIds = [];
        foreach ($logs as $log) {
            if ($log->old_class_id) $classIds[] = $log->old_class_id;
            if ($log->new_class_id) $classIds[] = $log->new_class_id;
        }
        $classIds = array_values(array_unique($classIds));
        $classes = CourseClass::whereIn('id', $classIds)->get()->keyBy('id');

        return view('admin.class-change-logs.index', compact('logs', 'classes'));
    }
}
