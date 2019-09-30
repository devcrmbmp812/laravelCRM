<?php

namespace App\Http\Controllers\Admin;

use App\ProjectActivity;
use App\Traits\FileSystemSettingTrait;
use App\UniversalSearch;
use App\UserActivity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class AdminBaseController extends Controller
{
    use FileSystemSettingTrait;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * UserBaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->companyName = $this->global->company_name;

        $this->middleware(function ($request, $next) {
            $this->setFileSystemConfigs();

            $this->emailSetting = email_notification_setting();
            $this->languageSettings = language_setting();
            $this->adminTheme = admin_theme();
            $this->pushSetting = push_setting();
            $this->smtpSetting = smtp_setting();

            $this->user = user();
            $this->modules = $this->user->modules;

            $this->unreadMessageCount = $this->user->user_chat->count();

            $data = \DB::table('notifications')
                ->select('type', \DB::raw('count(*) as total'))
                ->where('notifiable_id', $this->user->id)
                ->whereNull('read_at')
                ->groupBy('type')
                ->get();

            $counts = $data->groupBy('type');

            $type = 'App\Notifications\NewTicket';
            $this->unreadTicketCount = isset($counts[$type]) ? $counts[$type][0]->total : 0;


            $type = 'App\Notifications\NewExpenseAdmin';
            $this->unreadExpenseCount = isset($counts[$type]) ? $counts[$type][0]->total : 0;

            $type = 'App\Notifications\NewIssue';
            $this->unreadIssuesCount = isset($counts[$type]) ? $counts[$type][0]->total : 0;


            $this->stickyNotes = $this->user->sticky;

            return $next($request);
        });


    }

    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logUserActivity($userId, $text)
    {
        $activity = new UserActivity();
        $activity->user_id = $userId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logSearchEntry($searchableId, $title, $route)
    {
        $search = new UniversalSearch();
        $search->searchable_id = $searchableId;
        $search->title = $title;
        $search->route_name = $route;
        $search->save();
    }
}
