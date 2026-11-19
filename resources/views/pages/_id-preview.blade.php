{{-- On-page ID card preview (front + back), same layout as the PDF. $type = student|teacher --}}
@php
    $isT = $type === 'teacher';
    $public = $public ?? false;
    $school = $school ?? \App\Support\CardData::school();
    $logo = file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg'));
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('M d, Y') : '—';
    $session = \App\Support\CardData::session();
    $validUpTo = '30 Jun '.explode('-', $session)[1];
    $motto = trim((string) ($school['motto'] ?? ''));
    $serviceFor = function ($d) {
        if (! $d) { return '—'; }
        $diff = \Carbon\Carbon::parse($d)->diff(now()); $y = $diff->y; $m = $diff->m;
        $o = trim(($y ? $y.' year'.($y > 1 ? 's' : '') : '').($y && $m ? ', ' : '').($m ? $m.' month'.($m > 1 ? 's' : '') : ''));
        return $o ?: '< 1 month';
    };
    $rows = $isT ? [
        ['Staff ID', $idText], ['Designation', $person->designation ?: 'Staff'], ['PID / CNIC', $public ? \App\Support\CardData::mask($person->pid) : ($person->pid ?: ($person->cnic ?: '—'))],
        $public ? ['Status', 'Active Staff'] : ['Date of Birth', $fmt($person->dob)], ['Service', $serviceFor($person->entry_in_service)],
    ] : [
        ['Student ID', $idText], ['Class', $person->class.($person->section ? '-'.$person->section : '')], ['GR No.', $person->gr_number ?: '—'],
        $public ? ['Status', 'Enrolled'] : ['Date of Birth', $fmt($person->dob)], ['Session', $session],
    ];
    $emergency = $isT ? ($school['phone'] ?: '—') : ($public ? ($school['phone'] ?: '—') : (($person->guardian_phone ?: null) ?: ($school['phone'] ?: '—')));
@endphp
<div class="pc-wrap">
  <div class="pc-fit" data-w="458" data-h="288"><div class="pc-card">
    <div class="pc-h">
        <div class="pc-hn">{{ mb_strtoupper($school['name']) }}<small>@if ($motto !== ''){{ $motto }}@else Knowledge · Character · Excellence @endif</small></div>
        <img src="{{ $logo }}" alt="">
    </div>
    <div class="pc-b">
        <div class="pc-ph">@if ($person->photo_url)<img src="{{ $person->photo_url }}" alt="" onerror="this.replaceWith(Object.assign(document.createElement('b'),{textContent:'{{ mb_strtoupper(mb_substr($person->name ?? '?', 0, 1)) }}'}))">@else<b>{{ mb_strtoupper(mb_substr($person->name ?? '?', 0, 1)) }}</b>@endif</div>
        <div class="pc-i">
            <h4>{{ mb_strtoupper($person->name) }}</h4>
            @foreach ($rows as [$l, $v])<p><i>{{ $l }} :</i><b>{{ $v }}</b></p>@endforeach
        </div>
    </div>
    <div class="pc-f">
        @if ($isT)<div class="pc-sig"><span></span><small>STAFF SIGNATURE</small></div><small class="pc-emp">Employee of {{ $person->school_name ?: $school['name'] }}</small>
        @else<span class="pc-rib">STUDENT</span>@endif
        <div class="pc-hm"><em>Head Master</em><small>GBLSS GARHO</small></div>
    </div>
    <div class="pc-bar"></div>
    <div class="pc-ft">{{ $school['address'] }}@if ($school['phone']) &nbsp;|&nbsp; Tel: {{ $school['phone'] }}@endif @if ($school['email']) &nbsp;|&nbsp; {{ $school['email'] }}@endif</div>
  </div></div>

  <div class="pc-fit" data-w="458" data-h="288"><div class="pc-card pc-back">
    <div class="pc-h pc-h2"><img src="{{ $logo }}" alt=""><div class="pc-hn">{{ mb_strtoupper($school['name']) }}</div></div>
    <div class="pc-b2">
        <div class="pc-i2">
            <p><i>SEMIS ID :</i><b>{{ $school['code'] ?: '—' }}</b></p>
            <p><i>{{ $isT ? 'Employed At :' : 'Address :' }}</i><b>{{ $isT ? ($person->school_name ?: $school['name']) : ($public ? '—' : ($person->address ?: '—')) }}</b></p>
            <p><i>Valid Up To :</i><b>{{ $validUpTo }}</b></p>
        </div>
        <div class="pc-qr"><img src="{{ $qr }}" alt="QR"><small>{{ $isT ? 'Scan to verify staff ID' : 'Scan to verify / mark attendance' }}</small></div>
    </div>
    <div class="pc-tc"><div>TERMS &amp; CONDITIONS</div><ul>
        <li>This card is the property of {{ $school['name'] }} and is non-transferable.</li>
        <li>Loss of this card must be reported to the school office immediately.</li>
        <li>This card must be presented whenever required by school authorities.</li>
        <li>{{ $isT ? 'Must be surrendered upon leaving service.' : 'Valid only while the student is enrolled at this school.' }}</li>
    </ul></div>
    <div class="pc-em"><span>Emergency Contact</span><span>{{ $emergency }}</span></div>
    <div class="pc-bar"></div>
  </div></div>
</div>
