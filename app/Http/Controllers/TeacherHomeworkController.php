<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherHomeworkController extends Controller
{
    private function myClasses(): array
    {
        $t = auth()->user()->teacher;
        return $t ? collect($t->assigned_classes_list)->values()->all() : [];
    }

    public function index()
    {
        $classes = $this->myClasses();
        $items = Homework::whereIn('class_name', $classes)
            ->orderByDesc('date')->orderByDesc('created_at')->limit(50)->get();
        return view('teacher.homework', compact('classes', 'items'));
    }

    public function store(Request $r)
    {
        $classes = $this->myClasses();
        $data = $r->validate([
            'class_name' => ['required', 'in:' . implode(',', $classes)],
            'subject' => 'required|string|max:100',
            'text' => 'required|string|max:3000',
            'date' => 'required|date',
            'file' => 'nullable|file|max:5120',
        ]);
        $id = Str::random(10);
        $row = [
            'id' => $id,
            'date' => $data['date'],
            'text' => $data['text'],
            'status' => 'published',
            'subject' => $data['subject'],
            'class_name' => $data['class_name'],
            'teacher_name' => auth()->user()->name,
            'published_at' => now(),
        ];
        if ($r->hasFile('file')) {
            $f = $r->file('file');
            $name = $id . '_' . Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $f->getClientOriginalExtension();
            $row['attachment_path'] = $f->storeAs('homework', $name, 'public');
            $row['attachment_name'] = $f->getClientOriginalName();
            $row['attachment_type'] = $f->getClientMimeType();
        }
        Homework::create($row);
        return redirect()->route('teacher.homework.index')->with('ok', 'Homework save ho gaya.');
    }

    public function destroy(string $id)
    {
        $h = Homework::findOrFail($id);
        abort_unless(in_array($h->class_name, $this->myClasses()), 403);
        if ($h->attachment_path) {
            Storage::disk('public')->delete($h->attachment_path);
        }
        $h->delete();
        return redirect()->route('teacher.homework.index')->with('ok', 'Homework delete ho gaya.');
    }
}
