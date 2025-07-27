<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'complaints_count' => $this->whenCounted('complaints'),
            'complaints' => $this->whenLoaded('complaints', function () {
                return $this->complaints->map(function ($complaint) {
                    return [
                        'id' => $complaint->id,
                        'complaint_number' => $complaint->complaint_number,
                        'title' => $complaint->title,
                        'created_at' => $complaint->created_at->toISOString(),
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
