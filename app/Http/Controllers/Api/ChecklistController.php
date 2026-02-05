<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChecklistRequest;
use App\Http\Resources\ChecklistResource;
use App\Models\Checklist;

class ChecklistController extends Controller
{
    /**
     * Store a newly created checklist.
     */
    public function store(StoreChecklistRequest $request)
    {
        $maxPosition = Checklist::where('card_id', $request->card_id)->max('position') ?? -1;

        $checklist = Checklist::create([
            'card_id' => $request->card_id,
            'title' => $request->title,
            'position' => $maxPosition + 1,
        ]);

        return new ChecklistResource($checklist->load('items'));
    }

    /**
     * Remove the specified checklist.
     */
    public function destroy(Checklist $checklist)
    {
        $checklist->delete();

        return response()->json(['message' => 'Checklist deleted successfully']);
    }
}
