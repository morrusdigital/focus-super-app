<?php

namespace App\Services;

use App\Models\BudgetPlan;
use Illuminate\Support\Facades\DB;

class BudgetPlanService
{
    public function create(array $data, array $items, int $actorId): BudgetPlan
    {
        return DB::transaction(function () use ($data, $items, $actorId) {
            $bpNumber = $this->generateBpNumber();
            [$preparedItems, $totalAmount] = $this->prepareItems($items);

            $budgetPlan = BudgetPlan::create([
                ...$data,
                'bp_number' => $bpNumber,
                'total_amount' => $totalAmount,
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
            [$preparedItems, $totalAmount] = $this->prepareItems($items);

            $budgetPlan->fill([
                ...$data,
                'total_amount' => $totalAmount,
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

        foreach ($items as $item) {
            $harsat = (float) ($item['harsat'] ?? 0);
            $qty = (float) ($item['qty'] ?? 0);
            $jumlah = round($harsat * $qty, 2);

            $prepared[] = [
                'item_name' => $item['item_name'] ?? '',
                'kode' => $item['kode'] ?? '',
                'vendor_name' => $item['vendor_name'] ?? null,
                'harsat' => $harsat,
                'qty' => $qty,
                'satuan' => $item['satuan'] ?? '',
                'jumlah' => $jumlah,
            ];

            $totalAmount += $jumlah;
        }

        return [$prepared, round($totalAmount, 2)];
    }
}
