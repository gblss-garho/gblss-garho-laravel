@php
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('M d, Y') : '—';
    $gr = str_pad(trim((string) $student->gr_number), 3, '0', STR_PAD_LEFT);
    $admYear = $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->year : now()->year;
    $studentId = 'GBLSS-GARHO-'.$admYear.'-'.$gr;
    $session = app(\App\Services\SchoolScheduleService::class)->academicYearFor(now());
    $validUpTo = '30 Jun '.explode('-', $session)[1];
    $qr = app(\App\Services\QrCodeService::class)->dataUri($student->qr_code ?: $studentId, 300, 1);
    $photo = (string) $student->photo_url;
    $photoOk = preg_match('#^data:image/jpe?g;base64,#i', $photo) === 1;
    $emblem = public_path('images/lc/emblem.jpg');
    $class = $student->class.($student->section ? '-'.$student->section : '');
    $emergency = $student->guardian_phone ?: (($school['phone'] ?? null) ?: '—');
    $motto = ($school['motto'] ?? '') ?: 'Knowledge · Character · Excellence';
    $sname = mb_strtoupper($school['name']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Student ID Card</title>
<style>
    @page { margin: 20px 26px; }
    body { font-family: DejaVu Sans, sans-serif; color: #4A4237; font-size: 9.5px; margin: 0; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    .w { width: 100%; }
    .card { width: 458px; height: 288px; border: 1px solid #DDC77A; margin: 0 auto 26px; overflow: hidden; }
    .lb { width: 90px; white-space: nowrap; color: #8B7B1E; font-weight: bold; padding-bottom: 3px; }
    .vl { color: #123B7A; font-weight: bold; padding-bottom: 3px; }
    .lb2 { width: 72px; color: #8B7B1E; font-weight: bold; font-size: 9px; padding-bottom: 6px; }
    .vl2 { color: #123B7A; font-weight: bold; font-size: 10px; padding-bottom: 6px; }
</style>
</head>
<body>

<div class="card">
    <table class="w" style="background:#0B2C54;"><tr>
        <td style="width:60px;height:64px;"></td>
        <td style="text-align:center;vertical-align:middle;color:#FAF6EC;padding:6px 0;">
            <div style="font-size:11px;font-weight:bold;">{{ $sname }}</div>
            <div style="font-size:8px;color:#F0D989;margin-top:3px;">{{ $motto }}</div>
        </td>
        <td style="width:60px;text-align:right;vertical-align:middle;padding-right:12px;">
            @if (is_file($emblem))<img src="{{ $emblem }}" height="46" style="background:#fff;padding:2px;">@endif
        </td>
    </tr></table>
    <table class="w"><tr>
        <td style="width:104px;padding:14px 0 0 16px;">
            <div style="background:#DBB43C;padding:3px;width:96px;height:96px;">
                @if ($photoOk)
                    <img src="{{ $photo }}" width="96" height="96">
                @else
                    <div style="width:96px;height:70px;padding-top:26px;background:#EDE7D3;text-align:center;font-size:38px;color:#123B7A;">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</div>
                @endif
            </div>
        </td>
        <td style="padding:14px 14px 0 14px;">
            <div style="font-size:13px;font-weight:bold;color:#123B7A;margin-bottom:7px;">{{ mb_strtoupper($student->name) }}</div>
            <table>
                <tr><td class="lb">Student ID :</td><td class="vl">{{ $studentId }}</td></tr>
                <tr><td class="lb">Class :</td><td class="vl">{{ $class }}</td></tr>
                <tr><td class="lb">GR No. :</td><td class="vl">{{ $student->gr_number ?: '—' }}</td></tr>
                <tr><td class="lb">Date of Birth :</td><td class="vl">{{ $fmt($student->dob) }}</td></tr>
                <tr><td class="lb">Session :</td><td class="vl">{{ $session }}</td></tr>
            </table>
        </td>
    </tr></table>
    <table style="margin:12px 0 0 16px;"><tr>
        <td style="background:#DBB43C;color:#1F2A24;font-weight:bold;font-size:10px;letter-spacing:1px;padding:5px 22px 5px 14px;">STUDENT</td>
    </tr></table>
</div>

<div class="card">
    <table class="w" style="background:#0B2C54;"><tr>
        <td style="width:46px;height:46px;padding-left:14px;vertical-align:middle;">
            @if (is_file($emblem))<img src="{{ $emblem }}" height="30" style="background:#fff;padding:1px;">@endif
        </td>
        <td style="text-align:center;vertical-align:middle;color:#FAF6EC;font-size:10px;font-weight:bold;">{{ $sname }}</td>
        <td style="width:46px;"></td>
    </tr></table>
    <table class="w"><tr>
        <td style="padding:12px 0 0 16px;">
            <table>
                <tr><td class="lb2">SEMIS ID :</td><td class="vl2">{{ $school['code'] ?: '—' }}</td></tr>
                <tr><td class="lb2">Address :</td><td class="vl2">{{ $student->address ?: '—' }}</td></tr>
                <tr><td class="lb2">Valid Up To :</td><td class="vl2">{{ $validUpTo }}</td></tr>
            </table>
        </td>
        <td style="width:96px;padding:10px 16px 0 0;text-align:center;">
            <img src="{{ $qr }}" width="74" height="74" style="border:1px solid #E3DCC7;padding:3px;background:#fff;">
            <div style="font-size:6.5px;color:#8B8371;margin-top:2px;">Scan to verify student ID</div>
        </td>
    </tr></table>
    <div style="margin:8px 16px 0;border:1px solid #E3DCC7;background:#FBF9F2;">
        <div style="background:#123B7A;color:#FAF6EC;font-size:7.5px;font-weight:bold;text-align:center;padding:3px 0;">TERMS &amp; CONDITIONS</div>
        <ul style="font-size:6.5px;padding:0 0 0 22px;margin:4px 8px 4px 0;line-height:1.4;">
            <li>This card is the property of {{ $school['name'] }} and is non-transferable.</li>
            <li>Loss of this card must be reported to the school office immediately.</li>
            <li>This card must be presented whenever required by school authorities.</li>
            <li>Valid only while the student is enrolled at this school.</li>
        </ul>
    </div>
    <table class="w" style="background:#0B2C54;color:#F0D989;font-size:9px;font-weight:bold;margin-top:8px;"><tr>
        <td style="padding:7px 16px;">Emergency Contact</td>
        <td style="padding:7px 16px;text-align:right;">{{ $emergency }}</td>
    </tr></table>
    <div style="height:4px;background:#C9A227;"></div>
</div>

</body>
</html>
