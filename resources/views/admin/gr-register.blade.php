@extends('layouts.portal')

@section('title', 'GR Register')

@section('content')
    <p><a href="{{ route('admin.dashboard', [], false) }}">&larr; Dashboard</a></p>
    <div class="page-title" data-n="03"><h1>General Register (GR)</h1></div>
    <p class="sd sd-note">جنرل رجسٽر: GR نمبر 1 کان وٺي تازو داخلا تائين سمورا شاگرد.</p>

    <div class="fb-sum" style="flex-wrap:wrap">
        <span>Total: <b>{{ $total }}</b></span>
        <span>&middot; Active: <b>{{ $activeCount }}</b></span>
        <span>&middot; Left / Passed out: <b>{{ $total - $activeCount }}</b></span>
        <span>&middot; Last GR: <b>{{ $last }}</b></span>
    </div>

    @if ($empty)
        <div class="card">Purana register abhi import nahi hua, is liye sirf maujooda students dikh rahe hain. Server par <code>php84 artisan register:import</code> chalayein.</div>
    @endif

    <form method="GET" action="{{ route('admin.gr-register', [], false) }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Naam, father ya GR number" style="width:55%">
        <select name="f">
            <option value="all" @selected($f === 'all')>Sab (1 &ndash; {{ $last }})</option>
            <option value="active" @selected($f === 'active')>Active</option>
            <option value="left" @selected($f === 'left')>Left / Passed out</option>
        </select>
        <button type="submit">Talaash</button>
        <button type="button" onclick="window.print()">Print</button>
    </form>
    <p style="color:#6b6b5c;font-size:14px">{{ $shown }} record &middot; page {{ $page }} / {{ $pages }}</p>

    <div class="scroll"><table>
        <tr><th>GR</th><th>Name</th><th>Father</th><th>DOB</th><th>Address</th><th>Admitted</th><th>Removed</th><th>Status</th></tr>
        @forelse ($rows as $r)
            <tr>
                <td><b>{{ $r->reg }}</b></td>
                <td>{{ $r->name }}@if ($r->surname) <small>({{ $r->surname }})</small>@endif</td>
                <td>{{ $r->father }}</td>
                <td>{{ $r->dob ? \Illuminate\Support\Carbon::parse($r->dob)->format('d-m-Y') : '' }}</td>
                <td>{{ $r->address }}</td>
                <td>{{ $r->adm_date ? \Illuminate\Support\Carbon::parse($r->adm_date)->format('d-m-Y') : '' }}{{ $r->adm_class ? ' · '.$r->adm_class : '' }}</td>
                <td>
                    @if ($r->active)
                        &mdash;
                    @else
                        {{ $r->rem_date ? \Illuminate\Support\Carbon::parse($r->rem_date)->format('d-m-Y') : '' }}{{ $r->rem_class ? ' · '.$r->rem_class : '' }}
                        @if ($r->cause)<br><small>{{ $r->cause }}</small>@endif
                    @endif
                    @if ($r->remarks)<br><small>{{ $r->remarks }}</small>@endif
                </td>
                <td>{{ $r->active ? 'Active '.$r->current : ($r->status ?: 'Left') }}</td>
            </tr>
        @empty
            <tr><td colspan="8">Koi record nahi mila.</td></tr>
        @endforelse
    </table></div>

    <p>
        @if ($page > 1)<a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}">&larr; Pichla</a>@endif
        @if ($page < $pages) <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}">Agla &rarr;</a>@endif
    </p>
@endsection
