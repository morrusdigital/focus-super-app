<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskProjectTaskAssignee extends Pivot
{
    protected $table = 'task_project_task_assignees';
}
