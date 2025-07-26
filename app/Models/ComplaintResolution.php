<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintResolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'resolved_by',
        'resolution_notes',
        'internal_notes',
        'resolution_type',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    // Relationships
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
    
}
