<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintResolution;
use App\Models\ComplaintStatus;
use App\Models\User;
use App\Events\ComplaintCreated;
use App\Events\ComplaintAssigned;
use App\Events\ComplaintResolved;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ComplaintService
{
    public function getComplaints(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Complaint::with(['user:id,name,email', 'status', 'assignedUser:id,name'])
            ->select([
                'id', 'complaint_number', 'title', 'priority', 'category',
                'user_id', 'complaint_status_id', 'assigned_to', 'created_at'
            ]);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('complaint_status_id', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->assignedTo($filters['assigned_to']);
        }

        if (isset($filters['unassigned']) && $filters['unassigned']) {
            $query->unassigned();
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('complaint_number', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function createComplaint(array $data, User $user): Complaint
    {
        return DB::transaction(function () use ($data, $user) {
            // Get default status
            $defaultStatus = Cache::remember('default_complaint_status', 3600, function () {
                return ComplaintStatus::where('slug', 'open')->first()?->id ?? 1;
            });

            $complaint = Complaint::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'category' => $data['category'],
                'user_id' => $user->id,
                'complaint_status_id' => $defaultStatus,
            ]);

            // Handle file attachments
            if (!empty($data['attachments'])) {
                $this->handleAttachments($complaint, $data['attachments'], $user);
            }

            event(new ComplaintCreated($complaint));

            return $complaint->load(['user', 'status']);
        });
    }

    public function assignComplaint(Complaint $complaint, int $technicianId, User $assignedBy, ?string $notes = null): void
    {
        DB::transaction(function () use ($complaint, $technicianId, $assignedBy, $notes) {
            // Update complaint
            $complaint->update([
                'assigned_to' => $technicianId,
                'assigned_at' => now(),
            ]);

            // Create assignment record
            ComplaintAssignment::create([
                'complaint_id' => $complaint->id,
                'assigned_by' => $assignedBy->id,
                'assigned_to' => $technicianId,
                'notes' => $notes,
                'assigned_at' => now(),
            ]);

            // Update status to 'in-progress' if needed
            $inProgressStatus = Cache::remember('in_progress_status', 3600, function () {
                return ComplaintStatus::where('slug', 'in-progress')->first()?->id;
            });

            if ($inProgressStatus && $complaint->complaint_status_id !== $inProgressStatus) {
                $complaint->update(['complaint_status_id' => $inProgressStatus]);
            }

            event(new ComplaintAssigned($complaint, User::find($technicianId)));
        });
    }

    public function resolveComplaint(Complaint $complaint, array $resolutionData, User $resolvedBy): void
    {
        DB::transaction(function () use ($complaint, $resolutionData, $resolvedBy) {
            // Create resolution record
            ComplaintResolution::create([
                'complaint_id' => $complaint->id,
                'resolved_by' => $resolvedBy->id,
                'resolution_notes' => $resolutionData['resolution_notes'],
                'internal_notes' => $resolutionData['internal_notes'] ?? null,
                'resolution_type' => $resolutionData['resolution_type'],
                'resolved_at' => now(),
            ]);

            // Update complaint
            $resolvedStatus = Cache::remember('resolved_status', 3600, function () {
                return ComplaintStatus::where('slug', 'resolved')->first()?->id;
            });

            $complaint->update([
                'complaint_status_id' => $resolvedStatus,
                'resolved_at' => now(),
            ]);

            event(new ComplaintResolved($complaint));
        });
    }

    public function getDashboardStats(User $user): array
    {
        $cacheKey = "dashboard_stats_{$user->id}_{$user->getRoleNames()->first()}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            $baseQuery = $this->getBaseQueryForUser($user);
            
            return [
                'total' => (clone $baseQuery)->count(),
                'open' => (clone $baseQuery)->open()->count(),
                'resolved' => (clone $baseQuery)->resolved()->count(),
                'assigned_to_me' => $user->hasRole('technician') 
                    ? (clone $baseQuery)->assignedTo($user->id)->count() 
                    : null,
                'high_priority' => (clone $baseQuery)->byPriority('high')->open()->count(),
                'critical_priority' => (clone $baseQuery)->byPriority('critical')->open()->count(),
            ];
        });
    }

    private function getBaseQueryForUser(User $user)
    {
        $query = Complaint::query();

        if ($user->hasRole('user')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function handleAttachments(Complaint $complaint, array $files, User $user): void
    {
        foreach ($files as $file) {

            if (!$file || !$file->isValid()) {
                continue;
            }
            
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('complaints/' . $complaint->id, $fileName, 'public');

            $complaint->attachments()->create([
                'uploaded_by' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
