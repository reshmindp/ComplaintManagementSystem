<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResolutionResource extends JsonResource
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
            'resolution_notes' => $this->resolution_notes,
            'internal_notes' => $this->when(
                $request->user()->hasAnyRole(['admin', 'technician']),
                $this->internal_notes
            ),
            'resolution_type' => $this->resolution_type,
            'resolved_by' => $this->whenLoaded('resolvedBy', function () {
                return [
                    'id' => $this->resolvedBy->id,
                    'name' => $this->resolvedBy->name,
                ];
            }),
            'resolved_at' => $this->resolved_at->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
