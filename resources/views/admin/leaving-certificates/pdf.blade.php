@php
    $fmt = fn ($d) => $d ? $d->format('M d, Y') : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>School Leaving Certificate</title>
<style>
    @page { margin: 18px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; margin: 0; }
    .frame { border: 6px double #3f7f7a; padding: 22px 28px; height: 990px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .small { font-size: 10px; }
    h1 { font-size: 20px; margin: 6px 0 4px; }
    h2 { font-size: 17px; margin: 4px 0; }
    table.f { width: 100%; border-collapse: collapse;  }
    table.f td { padding: 7px 4px 2px; vertical-align: bottom; }
    td.lbl2 { padding-left: 8px; }
    td.val { border-bottom: 1px solid #000; text-align: center; font-weight: bold; }
    .dash { border-top: 1px dashed #666; margin-top: 14px; padding-top: 6px; }
    table.sig { width: 100%; border-collapse: collapse; margin-top: 0; }
    table.sig td.line { border-top: 1px solid #000; width: 40%; text-align: center; padding-top: 4px; }
    table.sig td.gap { width: 20%; }
</style>
</head>
<body>
<div class="frame">
    <div class="right small">LC No.: {{ $c->serial_no ? sprintf('LC-%04d', $c->serial_no) : '-' }}</div>
    <div class="center small">Form No.: 16</div>
    <div class="center"><h1>SCHOOL LEAVING CERTIFICATE</h1></div>
    <div class="center"><h2>{{ $school['name'] }}</h2></div>
    <div class="center"><strong>(SEMIS Code: {{ $school['code'] }})</strong></div>
    <div class="center small">{{ $school['address'] }}</div>

    <table class="f">
        <tr>
            <td width="26" style="width:26px;padding:0;height:0;font-size:0;line-height:0"></td>
            <td width="250" style="width:250px;padding:0;height:0;font-size:0;line-height:0"></td>
            <td style="padding:0;height:0;font-size:0;line-height:0"></td>
            <td width="190" style="width:190px;padding:0;height:0;font-size:0;line-height:0"></td>
            <td style="padding:0;height:0;font-size:0;line-height:0"></td>
        </tr>
        <tr><td colspan="2">School General Register No.:</td><td class="val">{{ $c->gr_number }}</td><td colspan="2"></td></tr>
        <tr><td>01</td><td>Student Name:</td><td class="val" colspan="3">{{ $c->name }}</td></tr>
        <tr><td>02</td><td>Father's Name:</td><td class="val" colspan="3">{{ $c->father_name }}</td></tr>
        <tr><td>03</td><td>Religion:</td><td class="val">{{ $c->religion }}</td><td class="lbl2">Race / Cast:</td><td class="val">{{ $c->caste }}</td></tr>
        <tr><td>04</td><td>Place of Birth:</td><td class="val" colspan="3">{{ $c->place_of_birth }}</td></tr>
        <tr><td>05</td><td>Date of Birth:</td><td class="val" colspan="3">{{ $fmt($c->dob) }}</td></tr>
        <tr><td></td><td>(In Words)</td><td class="val" colspan="3">{{ $c->dob_words }}</td></tr>
        <tr><td>06</td><td>Date of Admission:</td><td class="val">{{ $fmt($c->admission_date) }}</td><td class="lbl2">Class (in which Admitted):</td><td class="val">{{ $c->admitted_class }}</td></tr>
        <tr><td>07</td><td>Last School Attended:</td><td class="val" colspan="3">{{ $c->last_school }}</td></tr>
        <tr><td>08</td><td>Progress:</td><td class="val">{{ $c->progress }}</td><td class="lbl2">Conduct:</td><td class="val">{{ $c->conduct }}</td></tr>
        <tr><td>09</td><td>Date Leaving School:</td><td class="val" colspan="3">{{ $fmt($c->leaving_date) }}</td></tr>
        <tr><td>10</td><td>Class in which studying (or passed):</td><td class="val" colspan="3">{{ $c->passed_class }}</td></tr>
        <tr><td>11</td><td>Reason of Leaving School:</td><td class="val" colspan="3">{{ $c->reason }}</td></tr>
        <tr><td>12</td><td>School Dues (if any):</td><td class="val" colspan="3">{{ $c->dues }}</td></tr>
        <tr><td>13</td><td>Remarks:</td><td class="val" colspan="3">{{ $c->remarks }}</td></tr>
    </table>

    <div class="dash small">
        * Certified that the above information is in accordance with the School General Register.<br>
        * Date format: mmm dd, yyyy
    </div>

    <div class="center" style="margin-top:30px;">Date of Issue: <strong>{{ $fmt($c->issue_date) }}</strong></div>

    <div style="height:150px;"></div>
    <table class="sig">
        <tr>
            <td class="line">School Head Master</td>
            <td class="gap"></td>
            <td class="line">Taluka Education Officer</td>
        </tr>
    </table>
</div>
</body>
</html>
