@php
    $choice = old('status_choice', \App\Services\StudentStatusService::choiceFor($student ?? null, $gr ?? null));
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
@endphp
<fieldset style="border:1px solid #d9d4c3;border-radius:10px;padding:10px;margin:12px 0">
    <legend><b>Status (LC / Left / Passed out)</b></legend>
    <p>Status<br>
        <select name="status_choice">
            @foreach (\App\Services\StudentStatusService::STATUSES as $k => $v)
                <option value="{{ $k }}" @selected($choice === $k)>{{ $v }}</option>
            @endforeach
        </select></p>
    <p>Removal / LC date<br><input type="date" name="removal_date" value="{{ old('removal_date', $d($gr->removal_date ?? null)) }}"></p>
    <p>Removal class<br><input name="removal_class" value="{{ old('removal_class', $gr->removal_class ?? '') }}"></p>
    <p>Wajah (cause)<br><input name="removal_cause" value="{{ old('removal_cause', $gr->removal_cause ?? '') }}"></p>
    <p>Remarks<br><input name="remarks" value="{{ old('remarks', $gr->remarks ?? '') }}"></p>
    <small>Active chunne par student wapas active list mein aa jata hai aur removal details saaf ho jati hain.</small>
</fieldset>
