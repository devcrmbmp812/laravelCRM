<div class="media">
    <div class="media-body">
        <h5 class="media-heading"><span class="btn btn-circle btn-info"><i class="icon-list"></i></span> @lang('email.taskComplete.subject') - {{ ucfirst($notification->data['heading']) }}</h5>
       </div>
    <h6>@if(isset($notification->data['completed_on']))<i>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $notification->data['created_at'])->diffForHumans() }}</i>@endif</h6>
</div>