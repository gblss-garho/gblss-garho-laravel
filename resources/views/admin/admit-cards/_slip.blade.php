@php
    $logoFile = public_path('images/logo.png');
    $logo = is_file($logoFile) ? $logoFile : public_path('images/lc/emblem.jpg');
    $logoOk = is_file($logo);
    $examTitle = $school['exam_title'] ?? null;
    $examYear = $school['exam_year'] ?? null;
    $examLine = ($examTitle ?: '—').($examYear ? ' ('.$examYear.')' : '');
    $class = $student->class.($student->section ? '-'.$student->section : '');
    $photo = (string) \App\Support\PdfPhoto::small($student->photo_url);
    $photoOk = $photo !== '';
    $rows = [
        ['Exam', $examLine],
        ['Student Name', $student->name],
        ["Father's Name", $student->father_name],
        ['Class', $class],
        ['Roll No / GR No', $student->gr_number],
        ['Exam Centre', $school['name']],
    ];
@endphp
<div class="slip">
    <div class="hdr">
        @if ($logoOk)<div style="margin-bottom:6px;"><img src="{{ $logo }}" height="48" style="background:#fff;border:2px solid #DBB43C;padding:2px;"></div>@endif
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
