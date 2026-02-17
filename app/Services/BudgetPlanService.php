<?php

namespace App\Services;

use App\Models\BudgetPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetPlanService
{
    public function create(array $data, array $items, int $actorId): BudgetPlan
    {
        return DB::transaction(function () use ($data, $items, $actorId) {
            $bpNumber = $this->generateBpNumber();
            [$preparedItems, $totalAmount, $projectCount] = $this->prepareItems($items);
            $weekOfMonth = $this->calculateWeekOfMonth($data['submission_date'] ?? null);

            $budgetPlan = BudgetPlan::create([
                ...$data,
                'bp_number' => $bpNumber,
                'total_amount' => $totalAmount,
                'week_of_month' => $weekOfMonth,
                'project_count' => $projectCount,
            ]);

            if ($preparedItems) {
                $budgetPlan->items()->createMany($preparedItems);
            }

            $budgetPlan->logs()->create([
                'actor_id' => $actorId,
                'action' => 'created',
            ]);

            return $budgetPlan->fresh(['items', 'logs']);
        });
    }

    public function update(BudgetPlan $budgetPlan, array $data, array $items, int $actorId): BudgetPlan
    {
        return DB::transaction(function () use ($budgetPlan, $data, $items, $actorId) {
            [$preparedItems, $totalAmount, $projectCount] = $this->prepareItems($items);
            $weekOfMonth = $this->calculateWeekOfMonth($data['submission_date'] ?? $budgetPlan->submission_date);

            $budgetPlan->fill([
                ...$data,
                'total_amount' => $totalAmount,
                'week_of_month' => $weekOfMonth,
                'project_count' => $projectCount,
            ]);
            $budgetPlan->save();

            $budgetPlan->items()->delete();
            if ($preparedItems) {
                $budgetPlan->items()->createMany($preparedItems);
            }

            $budgetPlan->logs()->create([
                'actor_id' => $actorId,
                'action' => 'updated',
            ]);

            return $budgetPlan->fresh(['items', 'logs']);
        });
    }

    public function generateBpNumber(): string
    {
        $prefix = 'BP-' . now()->format('Ym') . '-';
        $lastNumber = BudgetPlan::where('bp_number', 'like', $prefix . '%')
            ->orderBy('bp_number', 'desc')
            ->value('bp_number');

        $nextSequence = 1;
        if ($lastNumber) {
            $nextSequence = (int) substr($lastNumber, -5) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }

    private function prepareItems(array $items): array
    {
        $prepared = [];
        $totalAmount = 0.0;
        $projectIds = [];

        foreach ($items as $item) {
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 0);
            $lineTotal = round($unitPrice * $quantity, 2);
            $projectId = $item['project_id'] ?: null;

            $prepared[] = [
                'project_id' => $projectId,
                'bank_account_id' => $item['bank_account_id'] ?: null,
                'chart_account_id' => $item['chart_account_id'] ?: null,
                'item_name' => $item['item_name'] ?? '',
                'vendor_name' => $item['vendor_name'] ?: null,
                'category' => $item['category'] ?: null,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'unit' => $item['unit'] ?: 'unit',
                'line_total' => $lineTotal,
                'real_amount' => (float) ($item['real_amount'] ?? 0),
            ];

            $totalAmount += $lineTotal;
            if ($projectId) {
                $projectIds[$projectId] = true;
            }
        }

        return [$prepared, round($totalAmount, 2), count($projectIds)];
    }

    private function calculateWeekOfMonth(null|string $date): ?int
    {
        if (! $date) {
            return null;
        }

        $carbon = Carbon::parse($date);
        $firstOfMonth = $carbon->copy()->startOfMonth();
        $offset = $firstOfMonth->dayOfWeekIso - 1; // Monday = 1
        $week = intdiv($offset + ($carbon->day - 1), 7) + 1;

        return max(1, min(6, $week));
    }
}
