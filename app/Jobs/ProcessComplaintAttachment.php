<?php

namespace App\Jobs;

use App\Models\ComplaintAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessComplaintAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ComplaintAttachment $attachment)
    {
    }

    public function handle(): void
    {
        if (!str_starts_with($this->attachment->mime_type, 'image/')) {
            return;
        }

        $filePath = Storage::disk('public')->path($this->attachment->file_path);
        
        if (!file_exists($filePath)) {
            return;
        }

        $thumbnailPath = str_replace('.', '_thumb.', $this->attachment->file_path);
        
        Image::make($filePath)
            ->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save(Storage::disk('public')->path($thumbnailPath));

        $this->attachment->update([
            'metadata' => array_merge($this->attachment->metadata ?? [], [
                'thumbnail_path' => $thumbnailPath
            ])
        ]);
    }
}
