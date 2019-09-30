<?php

namespace App\Http\Controllers\Member;

use App\Helper\Reply;
use App\Http\Requests\Tasks\StoreTask;
use App\Notifications\NewTask;
use App\Notifications\TaskCompleted;
use App\Notifications\TaskUpdated;
use App\Project;
use App\SubTask;
use App\Task;
use App\TaskboardColumn;
use App\TaskCategory;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class MemberProjectsController
 * @package App\Http\Controllers\Member
 */
class MemberTasksController extends MemberBaseController
{
    use ProjectProgress;

    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.projects');
        $this->middleware(function ($request, $next) {
            if(!in_array('tasks',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTask $request)
    {
        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();
        $task = new Task();
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        if (!$this->user->can('add_tasks') && $this->global->task_self == 'yes') {
            $task->user_id = $this->user->id;
        } else {
            $task->user_id = $request->user_id;
        }
        $task->project_id = $request->project_id;
        $task->priority = $request->priority;
        $task->task_category_id = $request->category_id;
        $task->board_column_id = $taskBoardColumn->id;
        $task->save();

//      Send notification to user
        $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->user_id);
        $notifyUser->notify(new NewTask($task));

        $this->logProjectActivity($request->project_id, __('messages.newTaskAddedToTheProject'));

        $this->project = Project::findOrFail($task->project_id);
        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        //log search
        $this->logSearchEntry($task->id, 'Task: '.$task->heading, 'admin.all-tasks.edit');

        return Reply::successWithData(__('messages.taskCreatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id);
        $this->categories = TaskCategory::all();
        // if($this->project->isProjectAdmin || $this->user->can('edit_projects'))
        //     $this->tasks = Task::where('project_id', $id)->get();
        // else
        //     $this->tasks = Task::where('project_id', $id)->where('user_id', $this->user->id)->get();

        return view('member.tasks.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->task = Task::findOrFail($id);
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->categories = TaskCategory::all();
        $view = view('member.tasks.edit', $this->data)->render();
        return Reply::dataOnly(['html' => $view]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTask $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        if (!$this->user->can('add_tasks') && $this->global->task_self == 'yes') {
            $task->user_id = $this->user->id;
        } else {
            $task->user_id = $request->user_id;
        }
        $task->priority = $request->priority;
        $task->board_column_id = $request->status;
        $task->task_category_id = $request->category_id;

        $taskBoardColumn = TaskboardColumn::findOrFail($request->status);
        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::now();
        } else {
            $task->completed_on = null;
        }

        $task->save();

        //Send notification to user
        $notifyUser = User::findOrFail($task->user_id);
        $notifyUser->notify(new TaskUpdated($task));

        //calculate project progress if enabled
        $this->calculateProjectProgress($request->project_id);

        $this->project = Project::findOrFail($task->project_id);

        $view = view('admin.projects.tasks.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.taskUpdatedSuccessfully'), ['html' => $view]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function changeStatus(Request $request)
    {
        $taskId = $request->taskId;
        $status = $request->status;

        $task = Task::findOrFail($taskId);

        if(auth()->user()->id == $task->user_id || $task->project->isProjectAdmin || $this->user->can('edit_tasks'))
        {
            $taskBoardColumn = TaskboardColumn::where('slug', $status)->first();

            $task->board_column_id = $taskBoardColumn->id;
            if($taskBoardColumn->slug == 'completed'){
                $task->completed_on = Carbon::today()->format('Y-m-d');
                $task->save();

                // send task complete notification
                $notifyUser = User::withoutGlobalScope('active')->findOrFail($task->user_id);
                $notifyUser->notify(new TaskCompleted($task));

                $admins = User::allAdmins($task->user_id);

                Notification::send($admins, new TaskCompleted($task));
            }else{
                $task->completed_on = null;
            }

            $task->save();

            if ($task->project_id != null) {
                if ($task->project->calculate_task_progress == "true") {
                    //calculate project progress if enabled
                    $this->calculateProjectProgress($task->project_id);
                }
                $this->project = Project::find($task->project_id);
                if($this->project->isProjectAdmin || $this->user->can('edit_tasks'))
                    $this->project->tasks = Task::where('project_id', $this->project->id)->orderBy($request->sortBy, 'desc')->get();
                else
                    $this->project->tasks = Task::where('project_id', $this->project->id)->orderBy($request->sortBy, 'desc')->where('user_id', $this->user->id)->get();            }

            $this->task = $task;

            $view = view('member.tasks.task-list-ajax', $this->data)->render();

            $this->logUserActivity($this->user->id, __('messages.taskUpdated').'<i>'.strtolower($task->board_column->column_name).'</i> : <strong>'.ucfirst($task->heading).'</strong>');

            return Reply::successWithData(__('messages.taskUpdatedSuccessfully'),  ['html' => $view, 'textColor' => $task->board_column->label_color, 'column' => $task->board_column->column_name]);

        }else{
            return Reply::error(Lang::get('messages.unAuthorisedUser'));
        }

    }

    public function sort(Request $request) {
        $projectId = $request->projectId;
        $this->sortBy = $request->sortBy;
        $taskBoardColumn = TaskboardColumn::where('slug', 'completed')->first();
        $this->project = Project::findOrFail($projectId);
        if($request->sortBy == 'due_date'){
            $order = "asc";
        }
        else{
            $order = "desc";
        }

        if($this->project->isProjectAdmin){
            $tasks = Task::whereProjectId($projectId)
                ->orderBy($request->sortBy, $order);
        }
        else{
            $tasks = Task::whereProjectId($projectId)
                ->where('user_id', $this->user->id)
                ->orderBy($request->sortBy, $order);
        }

        if($request->hideCompleted == '1'){
            $tasks = $tasks->where('board_column_id', '!=', $taskBoardColumn->id);
        }

//        $tasks = Task::whereProjectId($projectId)->orderBy($request->sortBy, $order);

        $this->project->tasks = $tasks->get();

        $view = view('member.tasks.task-list-ajax', $this->data)->render();

        return Reply::dataOnly(['html' => $view]);
    }

    public function checkTask($taskID){
        $task = Task::findOrFail($taskID);
        $subTask = SubTask::where('task_id', $taskID)->count();

        return Reply::dataOnly(['taskCount' => $subTask, 'lastStatus' => $task->board_column->slug]);
    }

    public function data(Request $request, $projectId = null) {

        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('users as client', 'client.id', '=', 'projects.client_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name', 'client.name as clientName', 'creator_user.name as created_by', 'creator_user.image as created_image', 'users.image', 'tasks.due_date', 'taskboard_columns.column_name', 'taskboard_columns.label_color', 'tasks.project_id', 'tasks.user_id as taskUserID')
            ->where('projects.id', $projectId);

        $tasks->get();

        return DataTables::of($tasks)
            ->addColumn('action', function($row){
                $action = '';
                
                if ($this->user->can('edit_tasks') || ($this->global->task_self == 'yes' && $this->user->id == $row->taskUserID)) {
                    $action.= '<a href="javascript:;" class="btn btn-info btn-circle edit-task"
                    data-toggle="tooltip" data-task-id="'.$row->id.'" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                if ($this->user->can('delete_tasks')) {
                    $action.= '&nbsp;&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-task-id="'.$row->id.'" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }

                return $action;
            })
            ->editColumn('due_date', function($row){
                if($row->due_date->isPast()) {
                    return '<span class="text-danger">'.$row->due_date->format($this->global->date_format).'</span>';
                }
                return '<span class="text-success">'.$row->due_date->format($this->global->date_format).'</span>';
            })
            ->editColumn('name', function($row){
                return ($row->image) ? '<img src="'.asset_url('avatar/'.$row->image).'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name) : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name);
            })
            ->editColumn('clientName', function($row){
                return ($row->clientName) ? ucwords($row->clientName) : '-';
            })
            ->editColumn('created_by', function($row){
                if(!is_null($row->created_by)){
                    return ($row->created_image) ? '<img src="'.asset_url('avatar/'.$row->created_image).'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->created_by) : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->created_by);
                }
                return '-';
            })
            ->editColumn('heading', function($row){
                return '<a href="javascript:;" data-task-id="'.$row->id.'" class="show-task-detail">'.ucfirst($row->heading).'</a>';
            })
            ->editColumn('column_name', function($row){
                return '<label class="label" style="background-color: '.$row->label_color.'">'.$row->column_name.'</label>';
            })
            ->rawColumns(['column_name', 'action', 'clientName', 'due_date', 'name', 'created_by', 'heading'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('created_image')
            ->removeColumn('label_color')
            ->make(true);
    }


}
