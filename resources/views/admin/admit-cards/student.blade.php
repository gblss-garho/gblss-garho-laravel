@php
    $examTitle = $school['exam_title'] ?? null;
    $examYear = $school['exam_year'] ?? null;
    $examLine = ($examTitle ?: '—').($examYear ? ' ('.$examYear.')' : '');
    $class = $student->class.($student->section ? '-'.$student->section : '');
    $photo = (string) $student->photo_url;
    $photoOk = preg_match('#^data:image/jpe?g;base64,#i', $photo) === 1;
    $rows = [
        ['Exam', $examLine],
        ['Student Name', $student->name],
        ["Father's Name", $student->father_name],
        ['Class', $class],
        ['Roll No / GR No', $student->gr_number],
        ['Exam Centre', $school['name']],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admit Card</title>
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
</head>
<body>
<div class="slip">
    <div class="hdr">
        <div style="font-weight:bold;font-size:14px;">{{ $school['name'] }}</div>
        <div style="font-size:9px;opacity:0.85;margin-top:2px;">{{ $school['address'] }}</div>
        <div class="badge">ADMIT CARD</div>
    </div>
    <table style="width:100%;padding:14px 16px;">
        <tr>
            <td style="width:70px;text-align:center;padding-top:14px;">
                <div style="border:2px solid #DBB43C;padding:2px;width:62px;">
                    @if ($photoOk)
                        <img src="{{ $photo }}" width="58" height="58">
                    @else
                        <div style="width:58px;height:42px;padding-top:16px;background:#F5F1E6;text-align:center;font-size:22px;color:#0F5132;">{{ mb_strtoupper(mb_substr($student->name ?? '?', 0, 1)) }}</div>
                    @endif
                </div>
                <div style="font-size:7px;color:#8B8371;margin-top:4px;">STUDENT PHOTO</div>
            </td>
            <td style="padding:14px 0 0 12px;">
                <table style="width:100%;">
                    @foreach ($rows as [$label, $val])
                        <tr class="row">
                            <td class="lbl">{{ $label }}</td>
                            <td>{{ $val ?: '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
    <div class="foot">Head Master Signature: __________</div>
    <div class="bar"></div>
</div>
</body>
</html>
