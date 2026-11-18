@extends('layouts.portal')

@section('title', 'Downloads')

@section('content')
    <style>
        .dl { max-width: 900px; }
        .dl-hero { border-radius:22px; overflow:hidden; color:#FAF6EC; margin:8px 0 14px; background:radial-gradient(130% 120% at 0% 0%,#1B7A4D 0%,#0F5132 55%,#0a3a24 100%); box-shadow:0 14px 34px rgba(11,61,38,.22); }
        .dl-hero i { display:block; height:9px; background:repeating-linear-gradient(90deg,#DBB43C 0 12px,#0F5132 12px 22px,#8A2232 22px 30px,#0F5132 30px 40px); }
        .dl-hero div { padding:14px 16px 16px; } .dl-hero b { font-family:'Noto Serif',Georgia,serif; font-size:22px; display:block; }
        .dl-hero span { font-size:13px; opacity:.85; }
        .dl h2 { font-size:17px; color:#0F5132; margin:22px 0 10px; }
        .dl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:10px; }
        .dl-tile { display:block; text-decoration:none; color:inherit; background:#fff; border:1px solid #E3DCC7; border-radius:16px; padding:13px 14px; }
        .dl-tile b { display:block; color:#0F5132; font-size:14.5px; } .dl-tile small { color:#7a7560; font-size:12px; }
        .dl-tile .ic { font-size:22px; display:block; margin-bottom:4px; }
        .dl-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
        .dl-chips a { text-decoration:none; font-size:12px; font-weight:700; padding:4px 10px; border-radius:999px; background:#EAF3EF; color:#0F5132; }
        .dl-card { background:#fff; border:1px solid #E3DCC7; border-radius:16px; padding:12px 14px; margin:8px 0; display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap; }
        .dl-card b { display:block; } .dl-card small { color:#7a7560; font-size:12px; display:block; }
        .dl-tag { font-size:11px; font-weight:800; padding:2px 9px; border-radius:999px; background:#FDF3D8; color:#8B7B1E; margin-right:6px; }
        .dl-tag.p { background:#DCFCE7; color:#14532D; }
        .dl-btn { font:inherit; font-weight:700; border:0; border-radius:12px; padding:9px 16px; background:#0F5132; color:#FAF6EC; cursor:pointer; text-decoration:none; font-size:14px; display:inline-block; }
        .dl-btn.red { background:#B91C1C; }
        .dl-form { display:flex; flex-wrap:wrap; gap:8px; background:#fff; border:1px solid #E3DCC7; border-radius:16px; padding:12px; }
        .dl-form input, .dl-form select, .dl-tools input, .dl-tools select { font:inherit; font-size:14px; padding:9px 10px; border:1px solid #D9D3BD; border-radius:12px; background:#FBF9F2; flex:1 1 150px; min-width:0; }
        .dl-tools { display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 10px; }
        .dl-ok { background:#DCFCE7; color:#14532D; border-radius:12px; padding:9px 12px; margin:8px 0; }
        .dl-er { background:#FEE2E2; color:#991B1B; border-radius:12px; padding:9px 12px; margin:8px 0; }
    </style>
    @php
        $r = fn ($n, $p = []) => route($n, $p, false);
        $fmt = fn ($b) => $b >= 1048576 ? round($b / 1048576, 1).' MB' : max(1, round($b / 1024)).' KB';
    @endphp
    <div class="dl">
        <section class="dl-hero"><i></i><div>
            <b>Downloads</b>
            <span>Print aur download ke liye sab kuch ek jagah.</span>
            <p class="sd" style="margin:6px 0 0">ڊائون لوڊ: پرنٽ ۽ ڊائون لوڊ لاءِ سڀ ڪجهه هڪ هنڌ.</p>
        </div></section>

        @if (session('ok'))<div class="dl-ok">{{ session('ok') }}</div>@endif
        @foreach ($errors->all() as $e)<div class="dl-er">{{ $e }}</div>@endforeach

        @if ($staff)
            <h2>Records print / download</h2>
            <div class="dl-grid">
                <a class="dl-tile" href="{{ $r($isAdmin ? 'admin.bookbank.index' : 'teacher.bookbank.index') }}"><span class="ic">📖</span><b>Book Bank register</b><small>Class chun kar Print Register dabayein</small></a>
                <a class="dl-tile" href="{{ $r($isAdmin ? 'admin.class-attendance' : 'teacher.class-attendance') }}"><span class="ic">📊</span><b>Students attendance record</b><small>Weekly / monthly (print kar sakte hain)</small></a>
                @if ($isAdmin)
                    <a class="dl-tile" href="{{ $r('admin.results.index') }}"><span class="ic">🏆</span><b>Results</b><small>Class-wise results</small></a>
                    <a class="dl-tile" href="{{ $r('admin.results.history') }}"><span class="ic">📊</span><b>Results history</b><small>Purane exams ke results</small></a>
                    <a class="dl-tile" href="{{ $r('admin.gr-register') }}"><span class="ic">📒</span><b>GR Register</b><small>Print button se register print/PDF</small></a>
                    <a class="dl-tile" href="{{ $r('admin.leaving-certificates.index') }}"><span class="ic">📄</span><b>Leaving Certificates</b><small>Student chun kar PDF</small></a>
                    <a class="dl-tile" href="{{ $r('admin.staff-id-cards.index') }}"><span class="ic">🪪</span><b>Staff ID Cards</b><small>Har teacher ka card PDF</small></a>
                    <div class="dl-tile"><span class="ic">🪪</span><b>Student ID Cards</b><small>Class-wise PDF</small>
                        <div class="dl-chips">@foreach ($classes as $c)<a href="{{ $r('admin.id-cards.class-pdf', ['class' => $c]) }}">{{ $c }}</a>@endforeach</div></div>
                    <div class="dl-tile"><span class="ic">🎫</span><b>Admit Cards</b><small>Class-wise PDF</small>
                        <div class="dl-chips">@foreach ($classes as $c)<a href="{{ $r('admin.admit-cards.class-pdf', ['class' => $c]) }}">{{ $c }}</a>@endforeach</div></div>
                @endif
            </div>
        @endif

        <h2>Parents ke liye (apne bachay ka document)</h2>
        <div class="dl-grid">
            <a class="dl-tile" href="{{ $r('public.results') }}"><span class="ic">📊</span><b>Result</b></a>
            <a class="dl-tile" href="{{ $r('public.id-cards') }}"><span class="ic">🪪</span><b>ID Card</b></a>
            <a class="dl-tile" href="{{ $r('public.admit-card') }}"><span class="ic">🎫</span><b>Admit Card</b></a>
            <a class="dl-tile" href="{{ $r('public.leaving-certificate') }}"><span class="ic">📜</span><b>Leaving Certificate</b></a>
        </div>

        <h2>Letters, notifications aur forms</h2>
        @if ($isAdmin)
            <form method="POST" action="{{ $r('downloads.store') }}" enctype="multipart/form-data" class="dl-form">
                @csrf
                <input name="title" placeholder="Title (e.g. Winter Vacation Notification)" required style="flex:2 1 240px">
                <select name="category">@foreach ($categories as $c)<option>{{ $c }}</option>@endforeach</select>
                <select name="audience"><option value="staff">Sirf staff</option><option value="public">Sab (public)</option></select>
                <input name="description" placeholder="Chhoti tafseel (optional)" style="flex:2 1 240px">
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt" style="flex:2 1 240px">
                <button class="dl-btn" type="submit">Upload</button>
                <small style="flex-basis:100%;color:#8a8470">PDF, Word, Excel, PowerPoint, JPG, PNG, TXT &middot; 10 MB tak.</small>
            </form>
        @endif

        <form method="GET" action="{{ $r('downloads.index') }}" class="dl-tools">
            <select name="cat" onchange="this.form.submit()"><option value="">Sab categories</option>@foreach ($categories as $c)<option @selected($cat === $c)>{{ $c }}</option>@endforeach</select>
            <input type="search" name="q" value="{{ $q }}" placeholder="Search">
            <button class="dl-btn" type="submit">Dhundein</button>
        </form>

        @forelse ($docs as $d)
            <div class="dl-card">
                <div>
                    <b>{{ $d->title }}</b>
                    <small><span class="dl-tag">{{ $d->category }}</span>@if ($d->audience === 'public')<span class="dl-tag p">Public</span>@elseif ($staff)<span class="dl-tag">Staff</span>@endif
                        {{ \Illuminate\Support\Carbon::parse($d->created_at)->format('d M Y') }} &middot; {{ $fmt($d->size) }}</small>
                    @if ($d->description)<small>{{ $d->description }}</small>@endif
                </div>
                <div style="display:flex;gap:6px">
                    <a class="dl-btn" href="{{ $r('downloads.file', ['id' => $d->id]) }}">&#11015; Download</a>
                    @if ($isAdmin)
                        <form method="POST" action="{{ $r('downloads.destroy', ['id' => $d->id]) }}" onsubmit="return confirm('Ye file hata dein?')">@csrf @method('DELETE')<button class="dl-btn red" type="submit">Hatayen</button></form>
                    @endif
                </div>
            </div>
        @empty
            <p class="sd">اڃا ڪا فائل اپلوڊ ناهي ڪئي وئي.</p>
        @endforelse
    </div>
@endsection
