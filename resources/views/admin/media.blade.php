@extends('layouts.app')

@section('title', 'Gallery, Textbooks & Feedback')

@section('content')
    <h1>Gallery, Textbooks &amp; Feedback</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif

    <h2>Gallery</h2>
    <form method="POST" action="{{ route('admin.media.gallery.add', [], false) }}" enctype="multipart/form-data">
        @csrf
        <p>Photo: <input type="file" name="image" accept="image/*"> ya link: <input name="url" placeholder="https://..."></p>
        <p><input name="caption" placeholder="Caption"> <button type="submit">Photo add karein</button></p>
    </form>
    <div style="display:flex;flex-wrap:wrap;gap:10px">
        @forelse ($gallery as $g)
            <div style="width:140px">
                <img src="{{ $g->url }}" alt="" style="width:140px;height:100px;object-fit:cover">
                <div style="font-size:12px">{{ $g->caption }}</div>
                <form method="POST" action="{{ route('admin.media.gallery.delete', $g->id, false) }}" onsubmit="return confirm('Ye photo hatani hai?')">
                    @csrf @method('DELETE')<button type="submit">Delete</button>
                </form>
            </div>
        @empty
            <p>Koi photo nahi.</p>
        @endforelse
    </div>

    <h2>Textbooks</h2>
    <form method="POST" action="{{ route('admin.media.textbook.add', [], false) }}">
        @csrf
        <input name="class_name" placeholder="Class (jaise VIII)" size="10" required>
        <input name="subject" placeholder="Subject" required>
        <input name="book_name" placeholder="Kitaab ka naam" required>
        <input name="book_url" placeholder="Link https://..." required>
        <button type="submit">Add karein</button>
    </form>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%;margin-top:10px">
        <tr><th>Class</th><th>Subject</th><th>Kitaab</th><th></th></tr>
        @forelse ($textbooks as $t)
            <tr>
                <td>{{ $t->class_name }}</td><td>{{ $t->subject }}</td>
                <td><a href="{{ $t->book_url }}" target="_blank">{{ $t->book_name }}</a></td>
                <td><form method="POST" action="{{ route('admin.media.textbook.delete', $t->id, false) }}" onsubmit="return confirm('Ye kitaab hatani hai?')">
                    @csrf @method('DELETE')<button type="submit">Delete</button></form></td>
            </tr>
        @empty
            <tr><td colspan="4">Koi kitaab nahi.</td></tr>
        @endforelse
    </table>

    <h2>Feedback</h2>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
        <tr><th>Naam</th><th>Rating</th><th>Paigham</th><th></th></tr>
        @forelse ($feedback as $f)
            <tr>
                <td>{{ $f->name }}</td><td>{{ $f->rating }}</td><td>{{ $f->message }}</td>
                <td><form method="POST" action="{{ route('admin.media.feedback.delete', $f->id, false) }}" onsubmit="return confirm('Ye feedback hatana hai?')">
                    @csrf @method('DELETE')<button type="submit">Delete</button></form></td>
            </tr>
        @empty
            <tr><td colspan="4">Koi feedback nahi.</td></tr>
        @endforelse
    </table>
@endsection
