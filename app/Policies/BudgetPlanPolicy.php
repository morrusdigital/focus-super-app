<?php

namespace App\Policies;

use App\Models\BudgetPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BudgetPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminCompany() || $this->isFinanceHolding($user) !== null;
    }

    public function view(User $user, BudgetPlan $budgetPlan): bool
    {
        if ($user->isAdminCompany()) {
            return $budgetPlan->company_id === $user->company_id;
        }

        $holdingId = $this->isFinanceHolding($user);
        if ($holdingId === null) {
            return false;
        }

        return $this->isUnderHolding($budgetPlan->company_id, $holdingId);
    }

    public function create(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function update(User $user, BudgetPlan $budgetPlan): bool
    {
        if (! $user->isAdminCompany()) {
            return false;
        }

        if ($budgetPlan->company_id !== $user->company_id) {
            return false;
        }

        return in_array($budgetPlan->status, [
            BudgetPlan::STATUS_DRAFT,
            BudgetPlan::STATUS_REVISION_REQUESTED,
        ], true);
    }

    public function delete(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->update($user, $budgetPlan);
    }

    public function submit(User $user, BudgetPlan $budgetPlan): bool
    {
        if (! $user->isAdminCompany()) {
            return false;
        }

        if ($budgetPlan->company_id !== $user->company_id) {
            return false;
        }

        return in_array($budgetPlan->status, [
            BudgetPlan::STATUS_DRAFT,
            BudgetPlan::STATUS_REVISION_REQUESTED,
        ], true);
    }

    public function approve(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->canFinanceAct($user, $budgetPlan);
    }

    public function reject(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->canFinanceAct($user, $budgetPlan);
    }

    public function requestRevision(User $user, BudgetPlan $budgetPlan): bool
    {
        return $this->canFinanceAct($user, $budgetPlan);
    }

    public function manageRealization(User $user, BudgetPlan $budgetPlan): bool
    {
        if (! $user->isAdminCompany()) {
            return false;
        }

        if ($budgetPlan->company_id !== $user->company_id) {
            return false;
        }

        return $budgetPlan->status === BudgetPlan::STATUS_APPROVED;
    }

    private function canFinanceAct(User $user, BudgetPlan $budgetPlan): bool
    {
        $holdingId = $this->isFinanceHolding($user);
        if ($holdingId === null) {
            return false;
        }

        if ($budgetPlan->status !== BudgetPlan::STATUS_SUBMITTED) {
            return false;
        }

        return $this->isUnderHolding($budgetPlan->company_id, $holdingId);
    }

    private function isFinanceHolding(User $user): ?int
    {
        if (! $user->isFinanceHolding() || $user->company_id === null) {
            return null;
        }

        $type = DB::table('companies')->where('id', $user->company_id)->value('type');
        if ($type !== 'holding') {
            return null;
        }

        return (int) $user->company_id;
    }

    private function isUnderHolding(int $companyId, int $holdingId): bool
    {
        $company = DB::table('companies')
            ->select('id', 'parent_id')
            ->where('id', $companyId)
            ->first();

        if (! $company) {
            return false;
        }

        if ((int) $company->id === $holdingId) {
            return true;
        }

        return (int) $company->parent_id === $holdingId;
    }
}
