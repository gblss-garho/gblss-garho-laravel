@php
    $photoOk = $teacher->photo_url && preg_match('/^data:image\/jpe?g;base64,/', $teacher->photo_url);
    $initial = strtoupper(substr($teacher->name ?? '?', 0, 1));
    $staffId = 'GBLSS-GARHO-STAFF-' . ($teacher->pid ?: strtoupper(substr($teacher->id, -6)));
    $dob = $teacher->dob ? $teacher->dob->format('d M Y') : '-';
    $joined = $teacher->entry_in_service ? $teacher->entry_in_service->format('d M Y') : '-';
    $qrValue = $teacher->qr_code ?: $staffId;
    $emblem = public_path('images/lc/emblem.jpg');
    $qr = app(\App\Services\QrCodeService::class)->dataUri($qrValue, 300, 1);
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 20px 26px; }
    body { font-family: 'DejaVu Sans', sans-serif; color: #123B7A; }
    .card { width: 458px; height: 288px; border: 1px solid #0B2C54; position: relative; margin-bottom: 24px; }
    .hdr { background: #0B2C54; color: #fff; padding: 6px 10px; }
    .hdr table { width: 100%; }
    .hdr .sname { font-size: 13px; font-weight: bold; text-transform: uppercase; }
    .hdr .motto { font-size: 8px; color: #DBB43C; }
    .ftr { background: #0B2C54; height: 8px; position: absolute; bottom: 0; left: 0; right: 0; }
    .photo-box { width: 96px; height: 96px; border: 3px solid #DBB43C; margin: 10px; }
    .photo-box img { width: 100%; height: 100%; }
    .placeholder { width: 90px; height: 90px; background: #C9A227; color: #fff; font-size: 36px; text-align: center; line-height: 90px; margin: 10px; }
    .name { font-size: 15px; font-weight: bold; color: #0B2C54; padding: 0 10px; }
    .tag { display: inline-block; background: #DBB43C; color: #0B2C54; font-size: 9px; font-weight: bold; padding: 2px 8px; margin: 4px 10px; }
    table.f { width: 100%; font-size: 9.5px; padding: 0 10px; }
    table.f td { padding: 3px 4px; }
    .lb { width: 90px; white-space: nowrap; font-weight: bold; }
    .qr { text-align: center; padding: 6px 0; }
    .terms { font-size: 7.5px; padding: 6px 10px; color: #333; }
    .sig { font-size: 9px; padding: 10px; }
</style>
</head>
<body>

<div class="card">
    <div class="hdr"><table><tr>
        <td style="width:40px">@if(is_file($emblem))<img src="{{ $emblem }}" style="width:34px;height:34px;">@endif</td>
        <td><div class="sname">{{ $school['name'] }}</div><div class="motto">{{ $school['motto'] ?? '' }}</div></td>
    </tr></table></div>

    @if($photoOk)
        <div class="photo-box"><img src="{{ $teacher->photo_url }}"></div>
    @else
        <div class="placeholder">{{ $initial }}</div>
    @endif

    <div class="name">{{ $teacher->name }}</div>
    <div class="tag">STAFF</div>

    <table class="f">
        <tr><td class="lb">Staff ID :</td><td>{{ $staffId }}</td></tr>
        <tr><td class="lb">Designation :</td><td>{{ $teacher->designation ?: '-' }}</td></tr>
        <tr><td class="lb">Subject :</td><td>{{ $teacher->subject ?: '-' }}</td></tr>
        <tr><td class="lb">Date of Birth :</td><td>{{ $dob }}</td></tr>
        <tr><td class="lb">Joining Date :</td><td>{{ $joined }}</td></tr>
    </table>
    <div class="ftr"></div>
</div>

<div class="card">
    <div class="hdr"><table><tr>
        <td style="width:40px">@if(is_file($emblem))<img src="{{ $emblem }}" style="width:34px;height:34px;">@endif</td>
        <td><div class="sname">{{ $school['name'] }}</div></td>
    </tr></table></div>

    <table class="f" style="padding-top:6px;">
        <tr><td class="lb">SEMIS ID :</td><td>{{ $school['code'] }}</td></tr>
        <tr><td class="lb">Address :</td><td>{{ $school['address'] }}</td></tr>
    </table>

    <div class="qr">
        <img src="{{ $qr }}" style="width:80px;height:80px;">
    </div>

    <div class="terms">
        This card is the property of {{ $school['name'] }}. If found, please return to the school office.
        Must be carried at all times on school premises and produced on request.
    </div>

    <div class="sig">Principal Signature: __________</div>
    <div class="ftr"></div>
</div>

</body>
</html>
