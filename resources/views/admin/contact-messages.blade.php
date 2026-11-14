@extends('layouts.portal')

@section('title', 'Contact Messages')

@section('content')
    <h1>Contact Messages ({{ count($messages) }})</h1>
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif

    @forelse ($messages as $m)
        <div class="fb-item">
            <b>{{ $m->name }}</b> &middot; <span>{{ $m->subject }}</span>
            <p dir="auto" style="margin:6px 0">{{ $m->message }}</p>
            <small>
                &#128222; <a href="tel:{{ preg_replace('/[^\d+]/', '', $m->phone) }}">{{ $m->phone }}</a>
                @if ($m->email) &middot; &#9993;&#65039; {{ $m->email }}@endif
                &middot; {{ substr((string) $m->created_at, 0, 16) }}
            </small>
            <form method="POST" action="{{ route('admin.contact.destroy', $m->id, false) }}" style="display:inline;margin-left:10px" onsubmit="return confirm('Ye message delete karein?')">
                @csrf @method('DELETE')
                <button type="submit" class="ghost" style="padding:4px 12px;color:#8B2E2E;border-color:#8B2E2E">Delete</button>
            </form>
        </div>
    @empty
        <div class="lk-card">Abhi koi message nahi aaya.</div>
    @endforelse
@endsection
