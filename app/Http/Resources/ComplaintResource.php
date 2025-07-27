<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'complaint_number' => $this->complaint_number,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'category' => $this->category,
            'status' => $this->whenLoaded('status', function () {
                return [
                    'id' => $this->status->id,
                    'name' => $this->status->name,
                    'slug' => $this->status->slug,
                    'color' => $this->status->color,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return $this->assignedUser ? [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                ] : null;
            }),
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            'resolution' => $this->whenLoaded('resolution', function () {
                return $this->resolution ? new ResolutionResource($this->resolution) : null;
            }),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'is_assigned' => $this->is_assigned,
            'is_resolved' => $this->is_resolved,
            'is_closed' => $this->is_closed,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
