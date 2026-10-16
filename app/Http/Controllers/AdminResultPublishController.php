<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminResultPublishController extends Controller
{
    public function toggle(Request $request)
    {
        $d = $request->validate([
            'class' => ['required', 'string'],
            'exam' => ['required', 'string'],
            'year' => ['nullable', 'string'],
        ]);

        $row = DB::table('result_publish_status')
            ->where('class_name', $d['class'])->where('exam_type', $d['exam'])->first();
        $publish = ! $row || $row->status !== 'published';
        $vals = [
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now()->toDateTimeString() : null,
            'published_by' => $request->user()->email,
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($row) {
            DB::table('result_publish_status')->where('id', $row->id)->update($vals);
        } else {
            DB::table('result_publish_status')->insert($vals + [
                'id' => Str::random(8), 'class_name' => $d['class'], 'exam_type' => $d['exam'],
                'created_at' => now()->toDateTimeString(),
            ]);
        }

        return redirect()->route('admin.results.index', [
            'year' => $d['year'] ?? null, 'exam' => $d['exam'], 'class' => $d['class'],
        ]);
    }
}
