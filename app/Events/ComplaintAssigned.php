<?php

namespace App\Events;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Complaint $complaint, public User $technician)
    {
        // The event is triggered when a complaint is assigned to a technician
        // It carries the complaint and the technician who is assigned to it
        $this->complaint = $complaint;
        $this->technician = $technician;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
