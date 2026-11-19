{{-- On-page admit card slip. $student, $school --}}
@php
    $logo = file_exists(public_path('images/logo-glow.png')) ? asset('images/logo-glow.png') : (file_exists(public_path('images/logo.png')) ? asset('images/logo.png') : asset('images/lc/emblem.jpg'));
    $exam = ($school['exam_title'] ?: '—').($school['exam_year'] ? ' ('.$school['exam_year'].')' : '');
    $rows = [['Exam', $exam], ['Student Name', $student->name], ["Father's Name", $student->father_name], ['Class', $student->class.($student->section ? '-'.$student->section : '')], ['Roll No / GR No', $student->gr_number], ['Exam Centre', $school['name']]];
    $ini = mb_strtoupper(mb_substr($student->name ?? '?', 0, 1));
@endphp
<div class="pc-fit" data-w="380" data-h="330"><div class="pc-slip">
    <div class="pc-sh"><img src="{{ $logo }}" alt=""><div><b>{{ $school['name'] }}</b><small>{{ $school['address'] }}</small></div></div>
    <div class="pc-sb"><span>ADMIT CARD</span></div>
    <div class="pc-sc">
        <div class="pc-sp">@if ($student->photo_url)<img src="{{ $student->photo_url }}" alt="" onerror="this.replaceWith(Object.assign(document.createElement('b'),{textContent:'{{ $ini }}'}))">@else<b>{{ $ini }}</b>@endif<small>STUDENT PHOTO</small></div>
        <table>@foreach ($rows as [$l, $v])<tr><th>{{ $l }}</th><td>{{ $v ?: '—' }}</td></tr>@endforeach</table>
    </div>
    <div class="pc-sf">Head Master Signature: __________</div>
    <div class="pc-sbar"></div>
</div></div>
