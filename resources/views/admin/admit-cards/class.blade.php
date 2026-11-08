<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admit Cards - Class {{ $class }}</title>
<style>
    @page { margin: 20px 26px; }
    body { font-family: DejaVu Sans, sans-serif; color: #1F2A24; font-size: 11px; margin: 0; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    .slip { width: 380px; border: 1px solid #C9A227; margin: 0 auto; }
    .hdr { background: #0B3D26; color: #FAF6EC; text-align: center; padding: 14px 16px; }
    .badge { display: inline-block; background: #16553533; border: 1px solid #DBB43C; color: #F0D989; font-size: 9px; font-weight: bold; letter-spacing: 1px; padding: 3px 12px; margin-top: 8px; }
    .row td { border-bottom: 1px dashed #E3DCC7; padding: 5px 0; }
    .lbl { width: 120px; font-weight: bold; color: #0F5132; }
    .foot { padding: 0 16px 14px; font-size: 10px; color: #4A4237; }
    .bar { height: 6px; background: #C9A227; }
</style>
<style>.slip-wrap { margin: 0 auto 16px; page-break-inside: avoid; }</style>
</head>
<body>
@foreach ($students as $student)
    <div class="slip-wrap">
        @include('admin.admit-cards._slip', ['student' => $student, 'school' => $school])
    </div>
@endforeach
</body>
</html>
