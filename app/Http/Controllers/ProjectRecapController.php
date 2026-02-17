<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectReceipt;
use Illuminate\Http\Request;

class ProjectRecapController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $projects = Project::query()
            ->when($user->isAdminCompany(), function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($user->isFinanceHolding(), function ($query) use ($user) {
                $query->whereHas('company', function ($companyQuery) use ($user) {
                    $companyQuery->where('parent_id', $user->company_id);
                });
            })
            ->with([
                'company',
                'receipts' => fn ($query) => $query
                    ->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED)
                    ->select(['id', 'project_id', 'amount', 'approval_status']),
            ])
            ->latest()
            ->get();

        $hppByProject = collect();
        if ($projects->isNotEmpty()) {
            $hppByProject = ProjectExpense::query()
                ->join('chart_accounts', 'chart_accounts.id', '=', 'project_expenses.chart_account_id')
                ->whereIn('project_expenses.project_id', $projects->pluck('id'))
                ->groupBy([
                    'project_expenses.project_id',
                    'project_expenses.chart_account_id',
                    'chart_accounts.code',
                    'chart_accounts.name',
                ])
                ->orderBy('project_expenses.project_id')
                ->orderBy('chart_accounts.code')
                ->selectRaw('
                    project_expenses.project_id,
                    project_expenses.chart_account_id,
                    chart_accounts.code,
                    chart_accounts.name,
                    SUM(project_expenses.amount) as total_amount
                ')
                ->get()
                ->groupBy('project_id');
        }

        return view('project_recaps.index', [
            'projects' => $projects,
            'hppByProject' => $hppByProject,
        ]);
    }
}
