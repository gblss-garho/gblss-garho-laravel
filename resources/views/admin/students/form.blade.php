@extends('layouts.portal')

@section('title', $student ? 'Edit Student' : 'Naya Student')

@section('content')
    <p><a href="{{ route('admin.students.index') }}">&larr; Students</a></p>
    <h1>{{ $student ? 'Student edit' : 'Naya student' }}</h1>

    @if ($errors->any())
        <ul style="color:red">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $student ? route('admin.students.update', $student) : route('admin.students.store') }}">
        @csrf
        @if ($student) @method('PUT') @endif
        @php
            $f = function ($k) use ($student) {
                $v = $student?->{$k};
                if ($v instanceof \Carbon\CarbonInterface) { $v = $v->format('Y-m-d'); }
                return old($k, $v);
            };
        @endphp
        <p>GR number<br><input name="gr_number" value="{{ $student ? $f('gr_number') : old('gr_number', $nextGr ?? '') }}" required inputmode="numeric"></p>
        <p>Naam<br><input name="name" value="{{ $f('name') }}" required></p>
        <p>Walid ka naam<br><input name="father_name" value="{{ $f('father_name') }}"></p>
        <p>Date of birth<br><input type="date" name="dob" value="{{ $f('dob') }}"></p>
        <p>Class<br><input name="class" value="{{ $f('class') }}" required></p>
        <p>Section<br><input name="section" value="{{ $f('section') }}"></p>
        <p>Address<br><input name="address" value="{{ $f('address') }}"></p>
        <p>Guardian phone <span class="sd">(مثال 923001234567، WhatsApp رپورٽ لاءِ)</span><br><input name="guardian_phone" value="{{ $f('guardian_phone') }}" placeholder="923001234567" ></p>
        <p>Admission date<br><input type="date" name="admission_date" value="{{ $f('admission_date') }}"></p>
        <div style="border:1px solid #d9d9d9;border-radius:8px;padding:10px;margin:10px 0">
            <b>Photo (ID card / Admit card)</b>
            @if ($student && $student->photo_url)
                <p><img src="{{ \Illuminate\Support\Str::startsWith($student->photo_url, ['data:','http','/']) ? $student->photo_url : '/'.$student->photo_url }}" alt="" style="width:90px;height:90px;object-fit:cover;object-position:center 20%;border-radius:6px;border:1px solid #ccc"><br>
                <label><input type="checkbox" name="remove_photo" value="1"> Photo hata dein</label></p>
            @endif
            <p>Photo upload karein (jpg/png)<br><input type="file" name="photo_file" accept="image/*"></p>
            <p>Ya photo ka link<br><input name="photo_link" value="{{ old('photo_link') }}" placeholder="https://..." style="width:100%"></p>
        </div>
        @if ($student)
            @include('admin.partials.status-fields', ['student' => $student, 'gr' => \Illuminate\Support\Facades\Schema::hasTable('gr_register') ? \Illuminate\Support\Facades\DB::table('gr_register')->where('reg_no', (int) $student->gr_number)->first() : null])
        @endif
        <button type="submit">Save</button>
    </form>
@endsection
