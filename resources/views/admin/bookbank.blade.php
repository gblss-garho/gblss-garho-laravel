@extends('layouts.portal')

@section('title', 'Book Bank')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/bookbank.css') }}">
    @php
        $nm = fn ($c) => preg_replace('/^class|\s+/i', '', mb_strtolower((string) $c));
        $schoolName = \Illuminate\Support\Facades\DB::table('school_info')->value('name') ?? 'Government Boys Lower Secondary School Garho';
        $keep = ['class' => $class];
    @endphp
    <div class="bb">
        <p class="bb-noprint" style="margin:0"><a href="{{ $back }}">&larr; Dashboard</a></p>
        <h1 class="bb-noprint" style="margin:4px 0 2px">Book Bank Register</h1>
        <p class="sd sd-note bb-noprint" style="margin:0">مٿي مان ڪلاس چونڊيو ته رجسٽر نظر اچي — ڪاغذي بوڪ بينڪ رجسٽر وانگر، هر ڪلاس جو رڪارڊ الڳ ڏٺو ويندو آهي.</p>

        @if (session('ok'))<div class="bb-ok bb-noprint">{{ session('ok') }}</div>@endif
        @foreach ($errors->all() as $e)<div class="bb-er bb-noprint">{{ $e }}</div>@endforeach

        @if (count($classes) === 0)
            <div class="bb-er">Koi class assign nahi hai.</div>
        @else
            <div class="bb-tabs">
                @foreach ($classes as $c)
                    <a href="{{ $base }}?class={{ urlencode($c) }}" @class(['on' => $nm($c) === $nm($class)])>{{ $c }}</a>
                @endforeach
            </div>

            <section class="bb-hero"><i></i><div>
                <b>{{ $class }} &mdash; Book Bank</b>
                <span>{{ $openCount }} kitaabein abhi issue &middot; {{ $classCatalog->count() }} titles &middot; {{ $students->count() }} students</span>
            </div></section>

            <div class="bb-card bb-noprint">
                <h2>Kitaab issue karein</h2>
                <form method="POST" action="{{ route($prefix.'.bookbank.issue', [], false) }}" class="bb-form">
                    @csrf
                    <input type="hidden" name="class" value="{{ $class }}">
                    <select name="catalog_id" required>
                        <option value="">Kitaab chunein</option>
                        @foreach ($classCatalog as $b)
                            @if ($b->available_qty > 0)<option value="{{ $b->id }}">{{ $b->subject }} &ndash; {{ $b->book_title }} ({{ $b->available_qty }})</option>@endif
                        @endforeach
                    </select>
                    <select name="student_id" required>
                        <option value="">Student chunein</option>
                        @foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }} (GR {{ $s->gr_number }})</option>@endforeach
                    </select>
                    <button type="submit">Issue</button>
                </form>
                @if ($classCatalog->isEmpty())<p style="color:#8a8470;font-size:13px">Is class ke liye abhi koi kitaab catalog mein nahi.@if ($isAdmin) Neeche se add karein.@else Admin se catalog mein add karwayein.@endif</p>@endif
            </div>

            <div class="bb-card">
                <div class="bb-pr">
                    <h2>{{ $schoolName }}</h2>
                    <p><b>Book Bank Register &mdash; {{ $class }}</b></p>
                    <p>Print date: {{ now()->format('d M Y') }}@if ($st !== 'all') &middot; {{ $st }}@endif</p>
                </div>
                <form method="GET" action="{{ $base }}" class="bb-tools">
                    <input type="hidden" name="class" value="{{ $class }}">
                    <select name="st" onchange="this.form.submit()">
                        <option value="all" @selected($st === 'all')>Sab record</option>
                        <option value="Issued" @selected($st === 'Issued')>Abhi issue (wapas nahi aayi)</option>
                        <option value="Returned" @selected($st === 'Returned')>Wapas aa chuki</option>
                    </select>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Student ya kitaab search">
                    <button class="bb-btn" type="submit">Dhundein</button>
                    <button class="bb-btn gold" type="button" onclick="window.print()">&#128424; Print Register</button>
                </form>
                <div class="bb-scroll">
                    <table class="bb-t">
                        <tr><th>#</th><th>Student</th><th>GR</th><th>Kitaab</th><th>Issue</th><th>Wapsi</th><th>Halat</th><th>Status</th><th class="bb-noprint"></th></tr>
                        @forelse ($issues as $n => $i)
                            <tr>
                                <td>{{ $n + 1 }}</td><td>{{ $i->student_name }}</td><td>{{ $grById[$i->student_id] ?? '' }}</td><td>{{ $i->book_title }}</td>
                                <td>{{ substr((string) $i->issue_date, 0, 10) }}</td><td>{{ substr((string) $i->return_date, 0, 10) }}</td>
                                <td>@if ($i->status === 'Returned')<span class="pill {{ $i->condition === 'Damaged' ? 'dm' : 'r' }}">{{ $i->condition }}</span>@endif</td>
                                <td><span class="pill {{ $i->status === 'Issued' ? 'i' : 'r' }}">{{ $i->status === 'Issued' ? 'Issued' : 'Returned' }}</span></td>
                                <td class="bb-noprint">
                                    @if ($i->status === 'Issued')
                                        <form method="POST" action="{{ route($prefix.'.bookbank.return', $i->id, false) }}" class="bb-form" style="flex-wrap:nowrap">
                                            @csrf
                                            <input type="hidden" name="class" value="{{ $class }}">
                                            <select name="condition" style="flex:0 0 90px"><option>Good</option><option>Fair</option><option>Damaged</option></select>
                                            <button type="submit">Wapas</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><span class="sd">هن ڪلاس جو ڪو رڪارڊ ناهي.</span></td></tr>
                        @endforelse
                    </table>
                </div>
            </div>

            <div class="bb-card">
                <h2>Stock (catalog){{ $isAdmin ? ' — sab classes' : '' }}</h2>
                <div class="bb-scroll">
                    <table class="bb-t">
                        <tr><th>Class</th><th>Subject</th><th>Kitaab</th><th>Maujood</th><th>Issue</th>@if ($isAdmin)<th class="bb-noprint"></th>@endif</tr>
                        @foreach ($catalog as $b)
                            <tr>
                                <td>{{ $b->class }}</td><td>{{ $b->subject }}</td><td>{{ $b->book_title }}</td>
                                <td>
                                    @if ($isAdmin)
                                        <form method="POST" action="{{ route('admin.bookbank.catalog.qty', $b->id, false) }}" class="bb-form bb-noprint" style="flex-wrap:nowrap">
                                            @csrf <input type="hidden" name="class" value="{{ $class }}">
                                            <input type="number" name="available_qty" value="{{ $b->available_qty }}" min="0" style="flex:0 0 70px">
                                            <button type="submit">Save</button>
                                        </form>
                                        <span class="bb-pr">{{ $b->available_qty }}</span>
                                    @else {{ $b->available_qty }} @endif
                                </td>
                                <td>{{ $b->issued_qty }}</td>
                                @if ($isAdmin)
                                    <td class="bb-noprint">
                                        <form method="POST" action="{{ route('admin.bookbank.catalog.delete', $b->id, false) }}" onsubmit="return confirm('Hata dein?')">
                                            @csrf @method('DELETE') <input type="hidden" name="class" value="{{ $class }}">
                                            <button type="submit" class="bb-btn red">Hatayen</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </div>
                @if ($isAdmin)
                    <h3 class="bb-noprint">Nayi kitaab</h3>
                    <form method="POST" action="{{ route('admin.bookbank.catalog.add', [], false) }}" class="bb-form bb-noprint">
                        @csrf
                        <input name="class" placeholder="Class (VI)" value="{{ $class }}" required style="flex:0 0 90px">
                        <input name="subject" placeholder="Subject" required>
                        <input name="book_title" placeholder="Kitaab ka naam" required>
                        <input type="number" name="available_qty" placeholder="Tadaad" min="0" required style="flex:0 0 90px">
                        <button type="submit">Add</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@endsection
