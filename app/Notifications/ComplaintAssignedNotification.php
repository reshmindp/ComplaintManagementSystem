<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Complaint $complaint)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isCustomer = $notifiable->id === $this->complaint->user_id;
        
        return (new MailMessage)
            ->subject('Complaint Assignment Update - ' . $this->complaint->complaint_number)
            ->greeting('Hello ' . $notifiable->name)
            ->line($isCustomer 
                ? 'Your complaint has been assigned to a technician.' 
                : 'A complaint has been assigned to you.')
            ->line('Complaint Number: ' . $this->complaint->complaint_number)
            ->line('Title: ' . $this->complaint->title)
            ->line('Priority: ' . ucfirst($this->complaint->priority))
            ->action('View Complaint', url('/complaints/' . $this->complaint->id))
            ->line('Thank you for using our complaint management system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'complaint_number' => $this->complaint->complaint_number,
            'title' => $this->complaint->title,
            'message' => 'Complaint assigned: ' . $this->complaint->complaint_number,
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}