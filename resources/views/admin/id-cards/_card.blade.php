{{-- Shared GBLSS ID card (front + back) for students and staff. DomPDF-safe: tables only. --}}
@php
    $isTeacher = $type === 'teacher';
    $logoFile = public_path('images/logo.png');
    $logo = is_file($logoFile) ? $logoFile : public_path('images/lc/emblem.jpg');
    $logoOk = is_file($logo);
    $photoOk = preg_match('#^data:image/jpe?g;base64,#i', (string) ($person->photo_url ?? '')) === 1;
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('M d, Y') : '—';
    $session = app(\App\Services\SchoolScheduleService::class)->academicYearFor(now());
    $validUpTo = '30 Jun '.explode('-', $session)[1];
    $motto = ($school['motto'] ?? '') ?: 'Knowledge · Character · Excellence';
    $sname = mb_strtoupper($school['name']);
    $schoolPhone = $school['phone'] ?? null;
    $emergency = $isTeacher ? ($schoolPhone ?: '—') : (($person->guardian_phone ?? null) ?: ($schoolPhone ?: '—'));
    $serviceFor = function ($d) {
        if (! $d) { return '—'; }
        $diff = \Carbon\Carbon::parse($d)->diff(now());
        $y = $diff->y; $m = $diff->m;
        $out = trim(($y ? $y.' year'.($y > 1 ? 's' : '') : '').($y && $m ? ', ' : '').($m ? $m.' month'.($m > 1 ? 's' : '') : ''));
        return $out ?: '< 1 month';
    };
    if ($isTeacher) {
        $rows = [
            ['Staff ID', $idText],
            [$person->designation ? 'Designation' : 'Staff', $person->designation ?: 'Staff'],
            ['PID / CNIC', $person->pid ?: ($person->cnic ?: '—')],
            ['Date of Birth', $fmt($person->dob)],
            ['Service', $serviceFor($person->entry_in_service)],
        ];
    } else {
        $rows = [
            ['Student ID', $idText],
            ['Class', $person->class.($person->section ? '-'.$person->section : '')],
            ['GR No.', $person->gr_number ?: '—'],
            ['Date of Birth', $fmt($person->dob)],
            ['Session', $session],
        ];
    }
    $initial = mb_strtoupper(mb_substr($person->name ?? '?', 0, 1));
@endphp
<style>
    @page { margin: 20px 26px; }
    body { font-family: DejaVu Sans, sans-serif; color: #4A4237; font-size: 9.5px; margin: 0; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    .w { width: 100%; }
    .card { width: 458px; height: 288px; border: 1px solid #DDC77A; margin: 0 auto 26px; overflow: hidden; background: #fff; }
    .lb { width: 86px; white-space: nowrap; color: #8B7B1E; font-weight: bold; font-size: 9.5px; padding-bottom: 3px; }
    .vl { color: #123B7A; font-weight: bold; font-size: 9.5px; padding-bottom: 3px; }
    .lb2 { width: 74px; color: #8B7B1E; font-weight: bold; font-size: 9px; padding-bottom: 6px; }
    .vl2 { color: #123B7A; font-weight: bold; font-size: 10px; padding-bottom: 6px; }
</style>

{{-- FRONT --}}
<div class="card">
    <table class="w" style="background:#123B7A;"><tr>
        <td style="width:58px;height:64px;"></td>
        <td style="text-align:center;vertical-align:middle;color:#FAF6EC;padding:6px 0;">
            <div style="font-size:11px;font-weight:bold;">{{ $sname }}</div>
            <div style="font-size:8px;color:#F0D989;margin-top:3px;">{{ $motto }}</div>
        </td>
        <td style="width:58px;text-align:right;vertical-align:middle;padding-right:10px;">
            @if ($logoOk)<img src="{{ $logo }}" height="46" style="background:#fff;border:2px solid #DBB43C;padding:1px;">@endif
        </td>
    </tr></table>
    <table class="w"><tr>
        <td style="width:108px;padding:12px 0 0 16px;">
            <div style="background:#DBB43C;padding:3px;width:96px;height:96px;">
                @if ($photoOk)
                    <img src="{{ $person->photo_url }}" width="96" height="96">
                @else
                    <div style="width:96px;height:70px;padding-top:26px;background:#EDE7D3;text-align:center;font-size:38px;color:#123B7A;">{{ $initial }}</div>
                @endif
            </div>
        </td>
        <td style="padding:12px 14px 0 12px;">
            <div style="font-size:12.5px;font-weight:bold;color:#123B7A;margin-bottom:7px;">{{ mb_strtoupper($person->name) }}</div>
            <table>
                @foreach ($rows as [$l, $v])
                    <tr><td class="lb">{{ $l }} :</td><td class="vl">{{ $v }}</td></tr>
                @endforeach
            </table>
        </td>
    </tr></table>
    <table class="w" style="margin-top:10px;"><tr>
        <td style="padding-left:16px;vertical-align:bottom;">
            @if ($isTeacher)
                <div style="width:96px;height:20px;"></div>
                <div style="width:96px;border-top:1.3px solid #123B7A;"></div>
                <div style="font-size:6.5px;color:#8B7B1E;font-weight:bold;margin-top:2px;">STAFF SIGNATURE</div>
            @else
                <span style="background:#DBB43C;color:#1F2A24;font-weight:bold;font-size:10px;padding:5px 22px 5px 14px;">STUDENT</span>
            @endif
        </td>
        @if ($isTeacher)
            <td style="font-size:7px;color:#8B8371;text-align:center;vertical-align:bottom;width:150px;">Employee of {{ $person->school_name ?: $school['name'] }}</td>
        @endif
        <td style="text-align:center;padding-right:16px;width:110px;vertical-align:bottom;">
            <div style="font-size:11px;color:#123B7A;font-style:italic;">Head Master</div>
            <div style="font-size:6.5px;color:#8B8371;border-top:1px solid #C9A227;margin-top:2px;padding-top:2px;">GBLSS GARHO</div>
        </td>
    </tr></table>
    <div style="height:4px;background:#C9A227;margin-top:7px;"></div>
    <table class="w" style="background:#0B2C54;color:#F0D989;font-size:6.5px;"><tr>
        <td style="padding:10px 8px;text-align:center;height:34px;">
            {{ $school['address'] ?? '' }}@if ($schoolPhone) &nbsp;|&nbsp; Tel: {{ $schoolPhone }}@endif @if (! empty($school['email'])) &nbsp;|&nbsp; {{ $school['email'] }}@endif
        </td>
    </tr></table>
</div>

{{-- BACK --}}
<div class="card">
    <table class="w" style="background:#123B7A;"><tr>
        <td style="width:46px;height:46px;padding-left:14px;vertical-align:middle;">
            @if ($logoOk)<img src="{{ $logo }}" height="30" style="background:#fff;padding:1px;">@endif
        </td>
        <td style="text-align:center;vertical-align:middle;color:#FAF6EC;font-size:10px;font-weight:bold;">{{ $sname }}</td>
        <td style="width:46px;"></td>
    </tr></table>
    <table class="w"><tr>
        <td style="padding:12px 0 0 16px;">
            <table>
                <tr><td class="lb2">SEMIS ID :</td><td class="vl2">{{ $school['code'] ?: '—' }}</td></tr>
                <tr><td class="lb2">{{ $isTeacher ? 'Employed At :' : 'Address :' }}</td><td class="vl2">{{ $isTeacher ? ($person->school_name ?: $school['name']) : ($person->address ?: '—') }}</td></tr>
                <tr><td class="lb2">Valid Up To :</td><td class="vl2">{{ $validUpTo }}</td></tr>
            </table>
        </td>
        <td style="width:96px;padding:10px 16px 0 0;text-align:center;">
            <img src="{{ $qr }}" width="74" height="74" style="border:1px solid #E3DCC7;padding:3px;background:#fff;">
            <div style="font-size:6.5px;color:#8B8371;margin-top:2px;">{{ $isTeacher ? 'Scan to verify staff ID' : 'Scan to verify / mark attendance' }}</div>
        </td>
    </tr></table>
    <div style="margin:8px 16px 0;border:1px solid #E3DCC7;background:#FBF9F2;">
        <div style="background:#123B7A;color:#FAF6EC;font-size:7.5px;font-weight:bold;text-align:center;padding:3px 0;">TERMS &amp; CONDITIONS</div>
        <ul style="font-size:6.5px;padding:0 0 0 22px;margin:4px 8px 4px 0;line-height:1.4;">
            <li>This card is the property of {{ $school['name'] }} and is non-transferable.</li>
            <li>Loss of this card must be reported to the school office immediately.</li>
            <li>This card must be presented whenever required by school authorities.</li>
            <li>{{ $isTeacher ? 'Must be surrendered upon leaving service.' : 'Valid only while the student is enrolled at this school.' }}</li>
        </ul>
    </div>
    <table class="w" style="background:#0B2C54;color:#F0D989;font-size:9px;font-weight:bold;margin-top:8px;"><tr>
        <td style="padding:7px 16px;">Emergency Contact</td>
        <td style="padding:7px 16px;text-align:right;">{{ $emergency }}</td>
    </tr></table>
    <div style="height:4px;background:#C9A227;"></div>
</div>
