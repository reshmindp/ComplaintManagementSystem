<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Events\ComplaintAssigned;
use App\Events\ComplaintResolved;
use App\Notifications\ComplaintCreatedNotification;
use App\Notifications\ComplaintAssignedNotification;
use App\Notifications\ComplaintResolvedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendComplaintNotifications
{
    public function handleComplaintCreated(ComplaintCreated $event): void
    {
        try {
            // Notify admins about new complaint
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new ComplaintCreatedNotification($event->complaint));
            }

            Log::info('Complaint created notifications sent', [
                'complaint_id' => $event->complaint->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send complaint created notifications', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleComplaintAssigned(ComplaintAssigned $event): void
    {
        try {
            // Notify the assigned technician
            $event->technician->notify(
                new ComplaintAssignedNotification($event->complaint)
            );

            // Notify the complaint owner
            $event->complaint->user->notify(
                new ComplaintAssignedNotification($event->complaint)
            );

            Log::info('Complaint assigned notifications sent', [
                'complaint_id' => $event->complaint->id,
                'technician_id' => $event->technician->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send complaint assigned notifications', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleComplaintResolved(ComplaintResolved $event): void
    {
        try {
            // Notify the complaint owner
            $event->complaint->user->notify(
                new ComplaintResolvedNotification($event->complaint)
            );

            Log::info('Complaint resolved notification sent', [
                'complaint_id' => $event->complaint->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send complaint resolved notification', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
