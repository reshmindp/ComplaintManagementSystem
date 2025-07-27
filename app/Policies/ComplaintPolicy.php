<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician', 'user']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        // Admins and technicians can view all complaints
        if ($user->hasAnyRole(['admin', 'technician'])) {
            return true;
        }

        // Users can only view their own complaints
        return $user->id === $complaint->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'technician', 'user']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        // Admins can update any complaint
        if ($user->hasRole('admin')) {
            return true;
        }

        // Technicians can update assigned complaints
        if ($user->hasRole('technician') && $complaint->assigned_to === $user->id) {
            return true;
        }

        // Users can update their own unresolved complaints
        if ($user->id === $complaint->user_id && !$complaint->is_resolved) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        // Only admins can delete complaints
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own unassigned, unresolved complaints
        return $user->id === $complaint->user_id 
            && !$complaint->is_assigned 
            && !$complaint->is_resolved;
    }

    public function assign(User $user, Complaint $complaint): bool
    {
        return $user->hasAnyRole(['admin', 'technician']);
    }

    public function resolve(User $user, Complaint $complaint): bool
    {
        // Admins can resolve any complaint
        if ($user->hasRole('admin')) {
            return true;
        }

        // Technicians can resolve assigned complaints
        return $user->hasRole('technician') && $complaint->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Complaint $complaint): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return false;
    }
}
