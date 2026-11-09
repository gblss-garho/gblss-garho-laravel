@extends('layouts.portal')

@section('title', 'School Settings')

@section('content')
    @php
        $v = fn ($k) => old($k, $s->$k ?? '');
        $field = function ($name, $label, $sd = '', $type = 'text', $ph = '') use ($v) {
            $out = '<label for="'.$name.'">'.e($label).($sd ? ' <span class="sd">'.$sd.'</span>' : '').'</label>';
            if ($type === 'textarea') {
                $out .= '<textarea id="'.$name.'" name="'.$name.'" rows="4" dir="auto">'.e($v($name)).'</textarea>';
            } else {
                $out .= '<input id="'.$name.'" name="'.$name.'" type="'.$type.'" value="'.e($v($name)).'" placeholder="'.e($ph).'"'.($type === 'number' ? ' step="any"' : '').'>';
            }
            return new \Illuminate\Support\HtmlString($out);
        };
        $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $checked = old('off', $off);
    @endphp

    <div class="page-title"><h1>School Settings</h1></div>
    <p class="sd sd-note">اسڪول جي تفصيل هتان تبديل ڪريو. تبديليون Home، About ۽ ڪارڊن تي فوري نظر ايندا.</p>
    @if (session('ok'))<div class="ok"><p>{{ session('ok') }}</p></div>@endif
    @if ($errors->any())<div class="err">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.settings.update', [], false) }}">
        @csrf

        <div class="card">
            <h2>School Details</h2>
            {{ $field('name', 'School Name') }}
            {{ $field('urdu_name', 'School Name (Sindhi)') }}
            {{ $field('school_photo_url', 'School Photo URL', '(Home page بينر لاءِ)', 'text', 'https://...') }}
            {{ $field('motto', 'Motto') }}
            {{ $field('about_text', 'About Us / School Description', "(Home page جي 'About Us' ٽيب لاءِ)", 'textarea') }}
            {{ $field('established_year', 'Established Year', '', 'number') }}
            {{ $field('semis_code', 'SEMIS Code') }}
            {{ $field('total_classes', 'Classes Offered') }}
        </div>

        <div class="card">
            <h2>Contact &amp; Location</h2>
            {{ $field('address', 'Address') }}
            {{ $field('map_location', 'Map Location', '(Google Plus Code يا جڳهه جو نالو، صحيح نقشي لاءِ)') }}
            {{ $field('phone', 'Phone') }}
            {{ $field('email', 'Email', '', 'email') }}
        </div>

        <div class="card">
            <h2>Time Clock &amp; Weekly Off Days</h2>
            <p class="sd sd-note">ٽيچر رڳو اسڪول جي ويجهو (radius اندر) Time In/Out ڪري سگهندو. Google Maps ۾ اسڪول پن تي دٻائي رکي coordinates ڪاپي ڪريو.</p>
            {{ $field('school_lat', 'School Latitude (e.g. 25.1234)', '', 'number') }}
            {{ $field('school_lng', 'School Longitude (e.g. 68.1234)', '', 'number') }}
            {{ $field('attendance_radius', 'Allowed Radius in Meters (default 200)', '', 'number') }}
            <p class="who" style="margin-top:12px">School ka waqt Sindh Government ke mutabiq khud lagta hai (sardi 1 Nov&ndash;31 Mar: 8:30 AM&ndash;2:00 PM, Jumma 12:30 PM tak; garmi 1 Apr&ndash;31 Oct: 8:00 AM&ndash;1:30 PM, Jumma 12:00 PM tak). Check-in pehle 30 minute, check-out aakhri 20 minute mein hota hai.</p>
            <label>Weekly Off Days</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach ($days as $n => $d)
                    <label style="text-transform:none;letter-spacing:0;font-family:inherit;font-size:15px;margin:0;color:var(--ink)"><input type="checkbox" name="off[]" value="{{ $n }}" @checked(in_array($n, array_map('intval', (array) $checked)))> {{ $d }}</label>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h2>Head Master</h2>
            {{ $field('principal_name', 'Head Master Name') }}
            {{ $field('principal_photo_url', 'Head Master Photo URL', '', 'text', 'https://...') }}
            {{ $field('principal_message', "Head Master's Message", '', 'textarea') }}
        </div>

        <div class="card">
            <h2>Taluka Education Officer</h2>
            {{ $field('teo_name', 'Taluka Education Officer — Name') }}
            {{ $field('teo_photo_url', 'Taluka Education Officer — Photo URL', '', 'text', 'https://...') }}
            {{ $field('teo_message', 'Taluka Education Officer — Message', '', 'textarea') }}
        </div>

        <div class="card">
            <h2>District Education Officer</h2>
            {{ $field('deo_name', 'District Education Officer — Name') }}
            {{ $field('deo_photo_url', 'District Education Officer — Photo URL', '', 'text', 'https://...') }}
            {{ $field('deo_message', 'District Education Officer — Message', '', 'textarea') }}
        </div>

        <div class="card">
            <h2>Current Exam</h2>
            {{ $field('current_exam_title', 'Current Exam Title (e.g. Annual Examination)') }}
            {{ $field('current_exam_year', 'Current Exam Year / Session (e.g. 2026-2027)') }}
        </div>

        <button type="submit">Save Changes</button>
        <a class="btn ghost" href="{{ route('admin.dashboard', [], false) }}">Cancel</a>
    </form>
@endsection
