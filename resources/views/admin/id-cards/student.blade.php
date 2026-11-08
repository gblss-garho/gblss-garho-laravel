@php
    $gr = str_pad(trim((string) $student->gr_number), 3, '0', STR_PAD_LEFT);
    $admYear = $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->year : now()->year;
    $idText = 'GBLSS-GARHO-'.$admYear.'-'.$gr;
    $qr = app(\App\Services\QrCodeService::class)->dataUri($student->qr_code ?: $idText, 300, 1);
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Student ID Card</title></head>
<body>
@include('admin.id-cards._card', ['type' => 'student', 'person' => $student, 'idText' => $idText, 'qr' => $qr])
</body>
</html>
