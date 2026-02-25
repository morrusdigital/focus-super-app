<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MyTaskController extends Controller
{
    // ---------------------------------------------------------------
    // GET /tasks/my
    // Tasks where auth user is an assignee, status != done.
    // Sorted: due_date ASC NULLS LAST, then updated_at DESC.
    // Company isolation applied per role.
    // ---------------------------------------------------------------

    public function my(Request $request)
    {
        $user = $request->user();

        $tasks = Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', '!=', TaskStatus::Done->value)
            ->with(['project', 'assignees'])
            ->tap(fn ($q) => $this->applyIsolation($q, $user))
            ->orderByRaw('due_date ASC NULLS LAST')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('tasks.my', compact('tasks'));
    }

    // ---------------------------------------------------------------
    // GET /tasks/overdue
    // Tasks where due_date < today AND due_date not null AND status != done.
    // Company isolation applied per role.
    // ---------------------------------------------------------------

    public function overdue(Request $request)
    {
        $user  = $request->user();
        $today = Carbon::today();

        $tasks = Task::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->where('status', '!=', TaskStatus::Done->value)
            ->with(['project', 'assignees'])
            ->tap(fn ($q) => $this->applyIsolation($q, $user))
            ->orderBy('due_date', 'asc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('tasks.overdue', compact('tasks'));
    }

    // ---------------------------------------------------------------
    // Private: apply role-based company isolation to a Task query.
    //
    //   holding_admin / finance_holding → all tasks (no filter)
    //   company_admin / finance_company → tasks in own company
    //   employee → tasks in projects they manage (PM) or are a member of
    // ---------------------------------------------------------------

    private function applyIsolation(\Illuminate\Database\Eloquent\Builder $query, $user): void
    {
        if ($user->isHoldingAdmin() || $user->isFinanceHolding()) {
            // No extra restriction.
            return;
        }

        if ($user->isCompanyAdmin() || $user->isFinanceCompany()) {
            $query->where('tasks.company_id', $user->company_id);
            return;
        }

        if ($user->isEmployee()) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('project', fn ($p) => $p->where('project_manager_id', $user->id))
                  ->orWhereHas('project.members', fn ($m) => $m->where('users.id', $user->id));
            });
            return;
        }

        // Fallback: return nothing.
        $query->whereRaw('1 = 0');
    }
}
