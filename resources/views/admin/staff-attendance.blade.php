@extends('layouts.portal')

@section('title', 'Staff Attendance Log')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/staff-attendance.css') }}">
    <div class="sa">
        <p style="margin:0"><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
        <h1 style="margin:4px 0 2px">Staff Attendance Log</h1>
        <p class="sa-note">Har teacher ka Time In / Time Out. Waqt hamesha server ki ghari se aata hai, phone ki ghari se nahi, is liye badla nahi ja sakta.</p>

        @unless ($geoSet)
            <div class="sa-warn">School ki location Settings mein set nahi hai, is liye abhi koi teacher hazri nahi laga sakta. <a href="{{ route('admin.settings.edit', [], false) }}">Settings kholein</a>.</div>
        @endunless

        <form method="GET" action="{{ route('admin.staff-attendance', [], false) }}" class="sa-pick">
            <label>Se <input type="date" name="from" value="{{ $from->toDateString() }}"></label>
            <label>Tak <input type="date" name="to" value="{{ $to->toDateString() }}"></label>
            <label>Teacher
                <select name="teacher">
                    <option value="">Sab</option>
                    @foreach ($teachers as $t)<option value="{{ $t->id }}" @selected($t->id === $teacherId)>{{ $t->name }}</option>@endforeach
                </select>
            </label>
            <button class="sa-btn" type="submit">Dikhayein</button>
        </form>

        <div class="sa-scroll"><table class="sa-t">
            <thead><tr>
                <th class="nm">Teacher</th>
                @unless ($single)<th>Tareekh</th>@endunless
                <th>Time In</th><th>Time Out</th><th>Ghante</th><th>Late</th><th>School se doori (In / Out)</th><th>Tasveer</th><th>Nishan</th>
            </tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr class="st-{{ $r['status'] }}">
                    <td class="nm">{{ $r['teacher']->name }}<small>{{ $r['teacher']->designation }}</small></td>
                    @unless ($single)<td>{{ $r['date']->format('d M') }}</td>@endunless
                    @if ($r['in'])
                        <td><b>{{ $r['in'] }}</b></td>
                        <td>{{ $r['out'] ?? '—' }}</td>
                        <td>{{ $r['hours'] ?? '—' }}</td>
                        <td>{{ $r['late'] > 0 ? $r['late'].' min' : 'Waqt par' }}</td>
                        <td>{{ $r['in_dist'] !== null ? $r['in_dist'].' m' : '—' }} / {{ $r['out_dist'] !== null ? $r['out_dist'].' m' : '—' }}@if ($r['in_acc'] !== null)<small>GPS &plusmn;{{ $r['in_acc'] }} m</small>@endif</td>
                        <td>@if ($r['in_photo'])<a href="{{ route('admin.staff-attendance.photo', ['id' => $r['id'], 'kind' => 'in'], false) }}" target="_blank" rel="noopener"><img class="sa-ph" loading="lazy" alt="In" src="{{ route('admin.staff-attendance.photo', ['id' => $r['id'], 'kind' => 'in'], false) }}"></a>@endif @if ($r['out_photo'])<a href="{{ route('admin.staff-attendance.photo', ['id' => $r['id'], 'kind' => 'out'], false) }}" target="_blank" rel="noopener"><img class="sa-ph" loading="lazy" alt="Out" src="{{ route('admin.staff-attendance.photo', ['id' => $r['id'], 'kind' => 'out'], false) }}"></a>@endif @unless ($r['in_photo'] || $r['out_photo'])—@endunless</td>
                        <td>@foreach ($r['flags'] as $f)<span class="sa-flag">{{ $f }}</span>@endforeach</td>
                    @else
                        <td colspan="7" class="sa-none">{{ ['absent' => 'Absent', 'leave' => 'Chhutti (approved)', 'off' => 'School band', 'pending' => 'Abhi baqi'][$r['status']] ?? '' }}</td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="9" class="sa-none">Is muddat mein koi record nahi.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <p class="sa-note">Tasveer: hazri ke waqt camera se li gayi, sirf admin ko dikhti hai aur 90 din baad khud hat jati hai. Peeche ki jagah dekhein: school hai ya ghar. Doori GPS se naapi jati hai. Hadd: school se {{ $radius }} m. "Nishan" mein wo cheezen aati hain jin par nazar rakhni chahiye.</p>

        <h3 class="sa-h">Refused koshishen</h3>
        <p class="sa-note">Jab kisi ne school se bahar se ya kamzor/ghalat location se hazri lagane ki koshish ki, ya chehra match nahi hua.</p>
        <div class="sa-scroll"><table class="sa-t">
            <thead><tr><th class="nm">Teacher</th><th>Waqt</th><th>Kya hua</th></tr></thead>
            <tbody>
            @forelse ($attempts as $a)
                <tr><td class="nm">{{ $a['name'] }}</td><td>{{ $a['at']->format('d M, h:i A') }}</td><td>{{ $a['what'] }}</td></tr>
            @empty
                <tr><td colspan="3" class="sa-none">Koi nahi.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
@endsection
