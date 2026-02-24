<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo    = 'todo';
    case Doing   = 'doing';
    case Blocked = 'blocked';
    case Done    = 'done';

    /**
     * Returns the human-readable label for each status.
     */
    public function label(): string
    {
        return match($this) {
            self::Todo    => 'To Do',
            self::Doing   => 'In Progress',
            self::Blocked => 'Blocked',
            self::Done    => 'Done',
        };
    }

    /**
     * Returns all status values as a plain array (useful for validation rules).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
