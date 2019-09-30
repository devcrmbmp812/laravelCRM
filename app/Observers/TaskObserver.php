<?php

namespace App\Observers;

use App\Task;

class TaskObserver
{

    public function saving(Task $task)
    {
//        $user = auth()->user();
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
//        if ($user) {
//            $task->created_by = $user->id;
//        }
    }
    public function creating(Task $task)
    {
        $user = auth()->user();
//         Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($user) {
            $task->created_by = $user->id;
        }
    }

}
