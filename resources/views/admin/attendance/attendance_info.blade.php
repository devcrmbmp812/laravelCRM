<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><i class="icon-clock"></i> @lang('app.menu.attendance') @lang('app.details')</h4>
</div>
<div class="modal-body">

    <div class="row">
        <div class="col-md-6">
            <div class="card punch-status">
                <div class="white-box">
                    <h4>@lang('app.menu.attendance') <small class="text-muted">{{ $startTime->format($global->date_format) }}</small></h4>
                    <div class="punch-det">
                        <h6>@lang('modules.attendance.clock_in')</h6>
                        <p>{{ $startTime->format($global->time_format) }}</p>
                    </div>
                    <div class="punch-info">
                        <div class="punch-hours">
                            <span>{{ $totalTime }} hrs</span>
                        </div>
                    </div>
                    <div class="punch-det">
                        <h6>@lang('modules.attendance.clock_out')</h6>
                        <p>{{ $endTime->format($global->time_format) }} 
                        @if (isset($notClockedOut))
                            (@lang('modules.attendance.notClockOut'))
                        @endif
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card recent-activity">
                <div class="white-box">
                    <h5 class="card-title">@lang('modules.employees.activity')</h5>
                    <ul class="res-activity-list">
                        @foreach ($attendanceActivity->reverse() as $item)
                            <li>
                                <p class="mb-0">@lang('modules.attendance.clock_in')</p>
                                <p class="res-activity-time">
                                    <i class="fa fa-clock-o"></i>
                                    {{ $item->clock_in_time->timezone($global->timezone)->format($global->time_format) }}.
                                </p>
                            </li>
                            <li>
                                <p class="mb-0">@lang('modules.attendance.clock_out')</p>
                                <p class="res-activity-time">
                                    <i class="fa fa-clock-o"></i>
                                    @if (!is_null($item->clock_out_time))
                                        {{ $item->clock_out_time->timezone($global->timezone)->format($global->time_format) }}.  
                                    @else
                                        @lang('modules.attendance.notClockOut')
                                    @endif
                                </p>
                            </li>
                                
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>