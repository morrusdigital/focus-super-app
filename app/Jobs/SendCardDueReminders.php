<?php

namespace App\Jobs;

use App\Models\Card;
use App\Notifications\CardDueSoonNotification;
use App\Notifications\CardOverdueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCardDueReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send notifications for cards due within 48 hours
        $cardsDueSoon = Card::whereNotNull('assignee_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addHours(48)])
            ->where('progress', '<', 100)
            ->with(['assignee', 'column.board.project'])
            ->get();

        foreach ($cardsDueSoon as $card) {
            // Check if notification was already sent today
            $alreadySent = $card->assignee->notifications()
                ->where('type', CardDueSoonNotification::class)
                ->where('data->card_id', $card->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadySent) {
                $card->assignee->notify(new CardDueSoonNotification($card));
            }
        }

        // Send notifications for overdue cards
        $overdueCards = Card::whereNotNull('assignee_id')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('progress', '<', 100)
            ->with(['assignee', 'column.board.project'])
            ->get();

        foreach ($overdueCards as $card) {
            // Check if notification was already sent today
            $alreadySent = $card->assignee->notifications()
                ->where('type', CardOverdueNotification::class)
                ->where('data->card_id', $card->id)
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadySent) {
                $card->assignee->notify(new CardOverdueNotification($card));
            }
        }
    }
}
