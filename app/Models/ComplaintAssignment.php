<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'assigned_by',
        'assigned_to',
        'notes',
        'assigned_at',
        'unassigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    // Relationships
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unassigned_at');
    }
}
