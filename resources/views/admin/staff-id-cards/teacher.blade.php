@php
    $year = $teacher->entry_in_service ? \Carbon\Carbon::parse($teacher->entry_in_service)->year : now()->year;
    $idText = \App\Support\CardData::staffId($teacher, $public ?? false);
    $qr = \App\Support\CardData::staffQr($teacher);
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Staff ID Card</title></head>
<body>
@include('admin.id-cards._card', ['type' => 'teacher', 'person' => $teacher, 'idText' => $idText, 'qr' => $qr, 'public' => $public ?? false])
</body>
</html>
