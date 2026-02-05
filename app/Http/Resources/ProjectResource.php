<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'budget' => $this->budget,
            'manager' => new UserResource($this->whenLoaded('manager')),
            'board' => new BoardResource($this->whenLoaded('board')),
            'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
