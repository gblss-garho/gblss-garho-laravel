<?php

namespace App\Http\Controllers;

use App\Models\PromotionBatch;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminPromotionController extends Controller
{
    public function history(): View
    {
        $batches = PromotionBatch::withCount([
            'logs as promoted_count' => fn ($q) => $q->where('outcome', 'promoted'),
            'logs as passed_out_count' => fn ($q) => $q->where('outcome', 'passed_out'),
            'logs as skipped_count' => fn ($q) => $q->where('outcome', 'like', 'skipped_%'),
        ])->orderByDesc('run_at')->get();

        return view('admin.promotions.history', compact('batches'));
    }

    public function review(PromotionBatch $batch): View
    {
        $flagged = $batch->logs()
            ->where('outcome', 'like', 'skipped_%')
            ->whereNull('undone_at')
            ->get();

        $students = Student::whereIn('id', $flagged->pluck('student_id'))->get()->keyBy('id');

        return view('admin.promotions.review', compact('batch', 'flagged', 'students'));
    }

    public function undo(PromotionBatch $batch): RedirectResponse
    {
        Artisan::call('promotion:undo', ['batch' => $batch->id]);

        return redirect()->route('admin.promotions.history')
            ->with('status', 'Batch #' . $batch->id . ' undone: ' . trim(Artisan::output()));
    }

    public function undoStudent(PromotionBatch $batch, string $student): RedirectResponse
    {
        Artisan::call('promotion:undo', ['batch' => $batch->id, '--student' => $student]);

        return redirect()->route('admin.promotions.review', $batch)
            ->with('status', trim(Artisan::output()));
    }
}
