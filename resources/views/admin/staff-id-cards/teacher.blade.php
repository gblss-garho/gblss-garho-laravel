@php
    $year = $teacher->entry_in_service ? \Carbon\Carbon::parse($teacher->entry_in_service)->year : now()->year;
    $idText = 'GBLSS-GARHO-STAFF-'.$year.'-'.strtoupper($teacher->pid ?: substr($teacher->id, -6));
    $qr = app(\App\Services\QrCodeService::class)->dataUri($teacher->qr_code ?: $idText, 300, 1);
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Staff ID Card</title></head>
<body>
@include('admin.id-cards._card', ['type' => 'teacher', 'person' => $teacher, 'idText' => $idText, 'qr' => $qr])
</body>
</html>
