<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistItemRequest;
use App\Http\Requests\UpdateChecklistItemRequest;
use App\Http\Resources\ChecklistItemResource;
use App\Models\ChecklistItem;

class ChecklistItemController extends Controller
{
    /**
     * Store a newly created checklist item.
     */
    public function store(StoreChecklistItemRequest $request)
    {
        $maxPosition = ChecklistItem::where('checklist_id', $request->checklist_id)->max('position') ?? -1;

        $item = ChecklistItem::create([
            'checklist_id' => $request->checklist_id,
            'title' => $request->title,
            'is_completed' => $request->is_completed ?? false,
            'position' => $maxPosition + 1,
        ]);

        return new ChecklistItemResource($item);
    }

    /**
     * Update the specified checklist item.
     */
    public function update(UpdateChecklistItemRequest $request, ChecklistItem $checklistItem)
    {
        $checklistItem->update($request->validated());

        return new ChecklistItemResource($checklistItem);
    }

    /**
     * Remove the specified checklist item.
     */
    public function destroy(ChecklistItem $checklistItem)
    {
        $checklistItem->delete();

        return response()->json(['message' => 'Checklist item deleted successfully']);
    }

    /**
     * Toggle the completion status of a checklist item.
     */
    public function toggle(ChecklistItem $checklistItem)
    {
        $checklistItem->update([
            'is_completed' => !$checklistItem->is_completed,
        ]);

        return new ChecklistItemResource($checklistItem);
    }
}
