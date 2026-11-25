{{-- Read-only weekly grid. $grid[day][period], $periods[period], $mode = 'class' | 'teacher' --}}
@php $today = (int) now()->dayOfWeekIso; @endphp
<div class="tt-scroll"><table class="tt-t">
    <thead><tr>
        <th class="tt-p">Period</th>
        @foreach (\App\Services\TimetableService::DAYS as $d => $name)
            <th @class(['tt-today' => $d === $today])>{{ $name }}</th>
        @endforeach
    </tr></thead>
    <tbody>
    @for ($p = 1; $p <= \App\Services\TimetableService::PERIODS; $p++)
        <tr>
            <th class="tt-p">{{ $p }}
                @if ($periods[$p]['starts_at'] || $periods[$p]['ends_at'])
                    <small>{{ $periods[$p]['starts_at'] }}&ndash;{{ $periods[$p]['ends_at'] }}</small>
                @endif
            </th>
            @foreach (\App\Services\TimetableService::DAYS as $d => $name)
                @php $e = $grid[$d][$p] ?? null; @endphp
                <td @class(['tt-today' => $d === $today, 'tt-empty' => ! $e])>
                    @if ($e)
                        <b>{{ $e->subject }}</b>
                        <small>{{ $mode === 'teacher' ? 'Class '.$e->class_name : ($e->teacher?->name ?? '') }}</small>
                    @endif
                </td>
            @endforeach
        </tr>
    @endfor
    </tbody>
</table></div>
