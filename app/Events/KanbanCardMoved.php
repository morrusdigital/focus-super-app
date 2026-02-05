<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KanbanCardMoved implements ShouldBroadcast
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
        return 'card.moved';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'card_id' => $this->card->id,
            'column_id' => $this->card->column_id,
            'position' => $this->card->position,
            'assignee' => $this->card->assignee ? [
                'id' => $this->card->assignee->id,
                'name' => $this->card->assignee->name,
            ] : null,
        ];
    }
}
