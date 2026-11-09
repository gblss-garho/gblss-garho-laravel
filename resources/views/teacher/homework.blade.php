@extends('layouts.portal')

@section('title', 'Homework')

@section('content')
    <h1>Homework</h1>
    <p class="sd sd-note">پنهنجي ڪلاس جو Homework / Diary لکو.</p>
    <p><a href="{{ route('teacher.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif

    @if (count($classes) === 0)
        <p class="sd">توهان جي ٽيچر رڪارڊ ۾ اڃا ڪا ڪلاس مقرر ناهي ٿيل. ايڊمن کان پنهنجي پروفائل ۾ 'Assigned Class' سيٽ ڪرايو.</p>
    @else
    <form method="POST" action="{{ route('teacher.homework.store', [], false) }}" enctype="multipart/form-data">
        @csrf
        <p><select name="class_name" required>
            @foreach ($classes as $c)<option value="{{ $c }}">Class {{ $c }}</option>@endforeach
        </select></p>
        <p><input name="subject" placeholder="Subject" required></p>
        <p><input type="date" name="date" value="{{ now()->toDateString() }}" required></p>
        <p><textarea name="text" rows="4" cols="40" placeholder="مثال: Exercise 3, Q1-10 حل ڪريو؛ سڀاڻي quiz ٿيندو." required></textarea></p>
        <p>File (optional, 5MB tak): <input type="file" name="file"></p>
        <p><button type="submit">Homework save karein</button></p>
    </form>
    @endif

    <h2>Pichla homework</h2>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Date</th><th>Class</th><th>Subject</th><th>Homework</th><th></th></tr>
        @forelse ($items as $h)
        <tr>
            <td>{{ $h->date->format('d-m-Y') }}</td>
            <td>{{ $h->class_name }}</td>
            <td>{{ $h->subject }}</td>
            <td>{{ $h->text }}
                @if ($h->attachment_path)<br><a href="{{ asset('storage/' . $h->attachment_path) }}" target="_blank">{{ $h->attachment_name ?: 'File' }}</a>@endif
            </td>
            <td>
                <form method="POST" action="{{ route('teacher.homework.destroy', $h->id, false) }}" onsubmit="return confirm('Ye homework delete karna hai?')">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5"><span class="sd">اڃا ڪو Homework/Diary داخل نه ڪيو ويو آهي.</span></td></tr>
        @endforelse
    </table>
@endsection
