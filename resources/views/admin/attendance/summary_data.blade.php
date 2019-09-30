<div class="white-box">
    <div class="table-responsive tableFixHead">
        <table class="table table-nowrap mb-0">
            <thead >
                <tr>
                    <th>@lang('app.employee')</th>
                    @for($i =1; $i <= $daysInMonth; $i++)
                        <th>{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @foreach($employeeAttendence as $key => $attendance)
                <tr>
                    {{-- <td> {{ substr($key, strripos($key,'#')+strlen('#')) }} </td> --}}
                    <td> {!! end($attendance) !!} </td>
                    @foreach($attendance as $key2=>$day)
                        @if ($key2+1 <= count($attendance))
                            <td class="text-center">
                                {!! $day !!}
                            </td>
                                
                        @endif
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>