<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_number',
        'title',
        'description',
        'priority',
        'category',
        'user_id',
        'complaint_status_id',
        'assigned_to',
        'assigned_at',
        'resolved_at',
        'closed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ComplaintStatus::class, 'complaint_status_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ComplaintAssignment::class);
    }

    public function resolution(): HasOne
    {
        return $this->hasOne(ComplaintResolution::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at')->whereNull('closed_at');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotNull('resolved_at');
    }

    public function getIsAssignedAttribute(): bool
    {
        return !is_null($this->assigned_to);
    }

    public function getIsResolvedAttribute(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function getIsClosedAttribute(): bool
    {
        return !is_null($this->closed_at);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($complaint) {
            if (empty($complaint->complaint_number)) {
                $complaint->complaint_number = static::generateComplaintNumber();
            }
        });
    }

    public static function generateComplaintNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastComplaint = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastComplaint ? 
            (int) substr($lastComplaint->complaint_number, -4) + 1 : 1;
        
        return "CMP{$year}{$month}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
