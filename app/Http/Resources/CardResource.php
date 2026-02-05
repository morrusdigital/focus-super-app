<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'column_id' => $this->column_id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee_id' => $this->assignee_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'position' => $this->position,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'progress' => (float) $this->progress,
            'labels' => $this->labels ?? [],
            'is_overdue' => $this->is_overdue,
            'checklists' => ChecklistResource::collection($this->whenLoaded('checklists')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
