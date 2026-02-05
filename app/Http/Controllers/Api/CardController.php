<?php

namespace App\Http\Controllers\Api;

use App\Events\KanbanCardMoved;
use App\Events\KanbanCardUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoveCardRequest;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Resources\CardResource;
use App\Models\Board;
use App\Models\Card;
use App\Models\Project;
use App\Notifications\CardAssignedNotification;
use App\Notifications\CardDueSoonNotification;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Display a listing of cards.
     */
    public function index(Project $project, Board $board)
    {
        $this->authorize('view', $board);

        $cards = Card::whereHas('column', function ($query) use ($board) {
            $query->where('board_id', $board->id);
        })
        ->with(['assignee', 'column', 'checklists.items'])
        ->orderBy('position')
        ->get();

        return CardResource::collection($cards);
    }

    /**
     * Store a newly created card.
     */
    public function store(StoreCardRequest $request, Project $project, Board $board)
    {
        $this->authorize('view', $board);

        // Get the max position in the target column
        $maxPosition = Card::where('column_id', $request->column_id)->max('position') ?? -1;

        $card = Card::create([
            'column_id' => $request->column_id,
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'position' => $maxPosition + 1,
            'priority' => $request->priority ?? 'medium',
            'due_date' => $request->due_date,
            'labels' => $request->labels,
        ]);

        // Send notification if assigned
        if ($card->assignee_id) {
            $card->assignee->notify(new CardAssignedNotification($card));
        }

        return new CardResource($card->load(['assignee', 'checklists.items']));
    }

    /**
     * Display the specified card.
     */
    public function show(Project $project, Board $board, Card $card)
    {
        $this->authorize('view', $card);

        return new CardResource($card->load(['assignee', 'column', 'checklists.items']));
    }

    /**
     * Update the specified card.
     */
    public function update(UpdateCardRequest $request, Project $project, Board $board, Card $card)
    {
        $oldAssigneeId = $card->assignee_id;

        $card->update($request->validated());

        // Send notification if assignee changed
        if ($request->has('assignee_id') && $request->assignee_id != $oldAssigneeId && $card->assignee_id) {
            $card->assignee->notify(new CardAssignedNotification($card));
        }

        // Broadcast card updated event
        broadcast(new KanbanCardUpdated($card->fresh(['assignee', 'column'])))->toOthers();

        return new CardResource($card->load(['assignee', 'checklists.items']));
    }

    /**
     * Remove the specified card.
     */
    public function destroy(Project $project, Board $board, Card $card)
    {
        $this->authorize('delete', $card);

        $columnId = $card->column_id;
        $position = $card->position;

        $card->delete();

        // Reorder remaining cards
        Card::where('column_id', $columnId)
            ->where('position', '>', $position)
            ->decrement('position');

        return response()->json(['message' => 'Card deleted successfully']);
    }

    /**
     * Move a card to a different column or position.
     */
    public function move(MoveCardRequest $request, Project $project, Board $board, Card $card)
    {
        DB::transaction(function () use ($request, $card) {
            $sourceColumnId = $card->column_id;
            $sourcePosition = $card->position;
            $targetColumnId = $request->target_column_id;
            $targetPosition = $request->target_position;

            // If moving within the same column
            if ($sourceColumnId == $targetColumnId) {
                if ($sourcePosition < $targetPosition) {
                    // Moving down
                    Card::where('column_id', $sourceColumnId)
                        ->where('position', '>', $sourcePosition)
                        ->where('position', '<=', $targetPosition)
                        ->decrement('position');
                } elseif ($sourcePosition > $targetPosition) {
                    // Moving up
                    Card::where('column_id', $sourceColumnId)
                        ->where('position', '>=', $targetPosition)
                        ->where('position', '<', $sourcePosition)
                        ->increment('position');
                }
            } else {
                // Moving to a different column
                // Decrement positions in source column
                Card::where('column_id', $sourceColumnId)
                    ->where('position', '>', $sourcePosition)
                    ->decrement('position');

                // Increment positions in target column
                Card::where('column_id', $targetColumnId)
                    ->where('position', '>=', $targetPosition)
                    ->increment('position');
            }

            // Update the card
            $card->update([
                'column_id' => $targetColumnId,
                'position' => $targetPosition,
            ]);
        });

        // Broadcast card moved event
        broadcast(new KanbanCardMoved($card->fresh(['assignee', 'column'])))->toOthers();

        return new CardResource($card->load(['assignee', 'column', 'checklists.items']));
    }
}
