@extends('layouts.portal')

@section('title', 'E-Library')

@section('content')
    @include('pages._hero', ['pill' => 'E-Library — Stories, Poems, Books, Videos & Songs'])
    @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
    @php $staff = auth()->check() && in_array(auth()->user()->role, ['admin', 'teacher'], true); @endphp

    <p class="sd sd-note">Padhen, khelen aur seekhen hamari activities ke sath.</p>

    @if (! $type)
        <div class="tiles">
            @foreach ($types as $key => [$label, $icon])
                <a class="tile" href="{{ route('public.elibrary', ['type' => $key], false) }}">
                    <b>{{ $icon }} {{ $label }}</b>
                    <small>{{ $counts[$key] ?? 0 }} item(s)</small>
                </a>
            @endforeach
        </div>
    @else
        <p><a href="{{ route('public.elibrary', [], false) }}">&larr; E-Library</a></p>
    @endif

    @if ($staff)
        <div class="lk-card">
            <div class="lk-title">{{ $edit ? 'Edit Item' : 'Add Item' }}</div>
            <form method="POST" action="{{ route('elibrary.store', [], false) }}">
                @csrf
                @if ($edit)<input type="hidden" name="id" value="{{ $edit->id }}">@endif
                <label for="f_type">Type</label>
                <select id="f_type" name="type">
                    @foreach ($types as $key => [$label])<option value="{{ $key }}" @selected(old('type', $edit->type ?? $type ?? 'story') === $key)>{{ $label }}</option>@endforeach
                </select>
                <label for="f_title">Title</label>
                <input id="f_title" name="title" required maxlength="200" value="{{ old('title', $edit->title ?? '') }}">
                <label for="f_media">Video / Song / Book link (optional)</label>
                <input id="f_media" name="media_url" type="url" placeholder="https://" value="{{ old('media_url', $edit->media_url ?? '') }}">
                <label for="f_img">Image link (optional)</label>
                <input id="f_img" name="image_url" type="url" placeholder="https://" value="{{ old('image_url', $edit->image_url ?? '') }}">
                <label for="f_content">Text (story / poem)</label>
                <textarea id="f_content" name="content" rows="5" dir="auto">{{ old('content', $edit->content ?? '') }}</textarea>
                <div class="btn-row"><button type="submit">Save</button>
                    @if ($edit)<a class="btn ghost" href="{{ route('public.elibrary', ['type' => $edit->type], false) }}">Cancel</a>@endif</div>
            </form>
        </div>
    @endif

    @if ($type)
        @forelse ($items as $f)
            @php $embed = \App\Http\Controllers\ElibraryController::youtubeEmbed($f->media_url); [$label, $icon, $cta] = $types[$f->type]; @endphp
            <div class="fb-item">
                <small>{{ $icon }} {{ $label }}</small>
                <h3 dir="auto" style="margin:4px 0 8px">{{ $f->title }}</h3>
                @if ($f->image_url)<img src="{{ $f->image_url }}" alt="" style="max-width:100%;border-radius:12px;margin-bottom:8px">@endif
                @if ($embed)
                    <div style="position:relative;padding-top:56%;margin-bottom:8px"><iframe src="{{ $embed }}" allowfullscreen loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;border:0;border-radius:12px"></iframe></div>
                @elseif ($f->media_url && $f->type === 'song' && preg_match('/\.(mp3|m4a|ogg|wav)(\?|$)/i', $f->media_url))
                    <audio controls src="{{ $f->media_url }}" style="width:100%;margin-bottom:8px"></audio>
                @elseif ($f->media_url)
                    <a class="btn" href="{{ $f->media_url }}" target="_blank" rel="noopener noreferrer">{{ $cta }}</a>
                @endif
                @if ($f->content)<p dir="auto" style="white-space:pre-wrap;line-height:1.7">{{ $f->content }}</p>@endif
                @if ($staff)
                    <a href="{{ route('public.elibrary', ['type' => $f->type, 'edit' => $f->id], false) }}">Edit</a>
                    <form method="POST" action="{{ route('elibrary.destroy', $f->id, false) }}" style="display:inline" onsubmit="return confirm('Ye item delete karein?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ghost" style="padding:2px 10px;color:#8B2E2E;border-color:#8B2E2E">Delete</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="sd sd-note">Is category mein abhi kuch nahi.</p>
        @endforelse
    @endif
@endsection
