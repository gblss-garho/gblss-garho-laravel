@extends('layouts.portal')

@section('title', 'Results')

@section('content')
    <p><a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a></p>
    <h1>Results</h1>
    <p class="sd sd-note">نمبر لکو يا "A" (غير حاضر) لاءِ فيلڊ خالي ڇڏيو. "Total" حاصل ٿيل مارڪس جو مجموعو آهي (صرف اهي مضمون جن ۾ نمبر ڏنا ويا آهن).</p>

    <form method="GET" action="{{ route('admin.results.index') }}">
        <select name="year">
            @foreach ($years as $y)<option value="{{ $y }}" @selected((string) $y === $year)>{{ $y }}</option>@endforeach
        </select>
        <select name="exam">
            @foreach ($exams as $e)<option value="{{ $e }}" @selected($e === $exam)>{{ $e }}</option>@endforeach
        </select>
        <select name="class">
            <option value="">-- Class chunein --</option>
            @foreach ($classes as $c)<option value="{{ $c }}" @selected($c === $class)>{{ $c }}</option>@endforeach
        </select>
        <button type="submit">Dikhayen</button>
    </form>

    @if ($class !== '')
        @php
            $ps = \Illuminate\Support\Facades\DB::table('result_publish_status')->where('class_name', $class)->where('exam_type', $exam)->value('status');
        @endphp
        <form method="POST" action="{{ route('admin.results.toggle') }}" style="margin-top:8px">
            @csrf
            <input type="hidden" name="class" value="{{ $class }}">
            <input type="hidden" name="exam" value="{{ $exam }}">
            <input type="hidden" name="year" value="{{ $year }}">
            Status: <b>{{ $ps === 'published' ? 'Published (parents ko nazar aata hai)' : 'Draft (chhupa hua)' }}</b>
            <button type="submit">{{ $ps === 'published' ? 'Unpublish karein' : 'Publish karein' }}</button>
        </form>
    @endif

    @if (session('ok'))<p style="color:green">{{ session('ok') }}</p>@endif
    @if ($errors->any())<p style="color:red">{{ $errors->first() }}</p>@endif
    <details style="margin:10px 0;border:1px solid #d9d9d9;border-radius:8px;padding:8px 12px">
        <summary><b>Archive &amp; Reset Marks</b> ({{ $exam }} {{ $year }})</summary>
        <p class="sd-note">Pehle CSV download karein, phir hi reset karein. Reset in marks ko hamesha ke liye hata deta hai.</p>
        <p><a href="{{ route('admin.results.archive', ['year' => $year, 'exam' => $exam]) }}">⬇ Marks CSV download</a></p>
        <form method="POST" action="{{ route('admin.results.reset') }}" onsubmit="return confirm('{{ $exam }} {{ $year }} ke SAB marks hata diye jayenge. Pakka?')">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}"><input type="hidden" name="exam" value="{{ $exam }}">
            <input name="confirm" placeholder="RESET likhein" autocomplete="off" style="width:140px">
            <button type="submit" style="background:#b00020;color:#fff">Reset marks</button>
        </form>
    </details>

    @php $q = ['year' => $year, 'exam' => $exam, 'class' => $class]; @endphp
    <div class="rtabs" style="display:flex;gap:6px;margin:14px 0 8px;flex-wrap:wrap">
        @foreach (['marks' => 'Marks & Rank', 'subjects' => 'Subject Averages', 'classes' => 'Class Summary'] as $k => $label)
            <a href="{{ route('admin.results.index', $q + ['tab' => $k]) }}" style="padding:6px 14px;border-radius:20px;border:1px solid #0B3D26;text-decoration:none;{{ $tab === $k ? 'background:#0B3D26;color:#fff' : 'color:#0B3D26' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($tab === 'classes')
        @if ($summary->isEmpty())
            <p class="sd">اهو امتحان / سال لاءِ ڪو نتيجو ناهي.</p>
        @else
            @php $maxAvg = max(1, (float) $summary->max('avg_subject')); @endphp
            <div style="overflow-x:auto">
            <table border="1" cellpadding="6" style="border-collapse:collapse">
                <tr><th>Class</th><th>Students</th><th>Avg total</th><th>Top total</th><th>Avg per subject</th><th></th></tr>
                @foreach ($summary as $c)
                    <tr>
                        <td><a href="{{ route('admin.results.index', ['year' => $year, 'exam' => $exam, 'class' => $c['class'], 'tab' => 'marks']) }}">{{ $c['class'] }}</a></td>
                        <td>{{ $c['students'] }}</td><td>{{ $c['avg_total'] }}</td><td>{{ $c['top_total'] }}</td><td>{{ $c['avg_subject'] }}</td>
                        <td style="min-width:140px"><div style="background:#DBB43C;height:12px;border-radius:6px;width:{{ round($c['avg_subject'] / $maxAvg * 100) }}%"></div></td>
                    </tr>
                @endforeach
            </table>
            </div>
        @endif
    @elseif ($class === '')
        <p>Class chun kar "Dikhayen" dabayein.</p>
    @elseif ($rows->isEmpty())
        <p class="sd">هن ڪلاس لاءِ ڪو اسٽوڊنٽ رڪارڊ ناهي.</p>
    @elseif ($tab === 'subjects')
        <h3>{{ $class }} — Subject average ({{ $exam }} {{ $year }})</h3>
        @php $top = max(1, (float) $subjectAvg->max('avg')); @endphp
        @foreach ($subjectAvg as $sub => $a)
            <div style="display:flex;align-items:center;gap:8px;margin:6px 0">
                <div style="width:110px;font-size:13px">{{ $sub }}</div>
                <div style="flex:1;background:#eee;border-radius:6px;overflow:hidden"><div style="background:#0F5132;height:18px;width:{{ round($a['avg'] / $top * 100) }}%"></div></div>
                <div style="width:90px;font-size:13px"><b>{{ $a['avg'] }}</b> <span style="color:#777">(max {{ $a['max'] }})</span></div>
            </div>
        @endforeach
    @else
        <div style="overflow-x:auto">
        <table border="1" cellpadding="6" style="border-collapse:collapse;margin-top:12px">
            <tr>
                <th>Rank</th><th>GR</th><th>Name</th>
                @foreach ($subjects as $s)<th>{{ $s }}</th>@endforeach
                <th>Total</th>
            </tr>
            @foreach ($rows as $i => $r)
                <tr>
                    <td>{{ $rank[$r['student']->id ?? $i] ?? '' }}</td>
                    <td>{{ $r['student']->gr_number ?? '' }}</td>
                    <td>{{ $r['student']->name ?? '' }}</td>
                    @foreach ($subjects as $s)<td>{{ $r['scores'][$s] ?? '-' }}</td>@endforeach
                    <td><b>{{ $r['total'] }}</b></td>
                </tr>
            @endforeach
        </table>
        </div>
    @endif
@endsection
