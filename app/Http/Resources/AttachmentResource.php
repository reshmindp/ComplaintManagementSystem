<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
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
            'original_name' => $this->original_name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'formatted_size' => $this->formatted_size,
            'url' => $this->url,
            'uploaded_by' => $this->whenLoaded('uploadedBy', function () {
                return [
                    'id' => $this->uploadedBy->id,
                    'name' => $this->uploadedBy->name,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
