<?php

namespace App\Notifications;

use App\Models\Card;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CardOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Card $card)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'card_overdue',
            'card_id' => $this->card->id,
            'card_title' => $this->card->title,
            'project' => $this->card->column->board->project->name,
            'due_date' => $this->card->due_date->format('Y-m-d'),
            'message' => "Card '{$this->card->title}' is overdue!",
            'url' => "/projects/{$this->card->column->board->project_id}/board",
        ];
    }
}
