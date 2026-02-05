<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KanbanCardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Card $card)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $boardId = $this->card->column->board_id;
        return [
            new Channel('board.' . $boardId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'card.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'card' => [
                'id' => $this->card->id,
                'title' => $this->card->title,
                'description' => $this->card->description,
                'column_id' => $this->card->column_id,
                'assignee' => $this->card->assignee ? [
                    'id' => $this->card->assignee->id,
                    'name' => $this->card->assignee->name,
                ] : null,
                'priority' => $this->card->priority,
                'due_date' => $this->card->due_date?->format('Y-m-d'),
                'progress' => (float) $this->card->progress,
                'labels' => $this->card->labels ?? [],
            ],
        ];
    }
}
