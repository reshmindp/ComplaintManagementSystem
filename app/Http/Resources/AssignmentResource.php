<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
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
            'assigned_by' => $this->whenLoaded('assignedBy', function () {
                return [
                    'id' => $this->assignedBy->id,
                    'name' => $this->assignedBy->name,
                ];
            }),
            'assigned_to' => $this->whenLoaded('assignedTo', function () {
                return [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                ];
            }),
            'notes' => $this->notes,
            'assigned_at' => $this->assigned_at->toISOString(),
            'unassigned_at' => $this->unassigned_at?->toISOString(),
            'is_active' => is_null($this->unassigned_at),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
