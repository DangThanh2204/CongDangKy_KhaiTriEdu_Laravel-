<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseEnrollment;
use App\Models\Payment;

class MapEnrollmentsClassId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:map-enrollments-classid {--batch=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map class_id into course_enrollments using payments as source of truth';

    public function handle()
    {
        $this->info('Starting mapping of course_enrollments.class_id from payments...');

        $batch = (int)$this->option('batch') ?: 1000;

        $query = CourseEnrollment::whereNull('class_id');
        $total = $query->count();
        $this->info("Found {$total} enrollments with NULL class_id");

        $processed = 0;

        $query->chunkById($batch, function ($rows) use (&$processed) {
            foreach ($rows as $enrollment) {
                // Try mapping via payments: find completed payment for same user and course with class_id
                $payment = Payment::where('user_id', $enrollment->user_id)
                    ->where(function ($q) use ($enrollment) {
                        $q->where('course_id', $enrollment->course_id);
                    })
                    ->whereNotNull('class_id')
                    ->where('status', 'completed')
                    ->orderBy('paid_at', 'desc')
                    ->first();

                if ($payment && $payment->class_id) {
                    $enrollment->class_id = $payment->class_id;
                    $enrollment->save();
                    $processed++;
                    continue;
                }

                // Fallback: try any payment with class_id for user and course (even pending)
                $payment = Payment::where('user_id', $enrollment->user_id)
                    ->where('course_id', $enrollment->course_id)
                    ->whereNotNull('class_id')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($payment && $payment->class_id) {
                    $enrollment->class_id = $payment->class_id;
                    $enrollment->save();
                    $processed++;
                    continue;
                }

                // No mapping found — leave for manual review
            }
        });

        $this->info("Processed: {$processed}");
        $remaining = CourseEnrollment::whereNull('class_id')->count();
        $this->info("Remaining enrollments with NULL class_id: {$remaining}");

        $this->info('Mapping finished. Review remaining records and run again after manual fixes.');

        return 0;
    }
}
