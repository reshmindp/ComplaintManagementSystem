<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintCreatedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Complaint Created - ' . $this->complaint->complaint_number)
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new complaint has been created.')
            ->line('Complaint Number: ' . $this->complaint->complaint_number)
            ->line('Title: ' . $this->complaint->title)
            ->line('Priority: ' . ucfirst($this->complaint->priority))
            ->line('Category: ' . ucfirst($this->complaint->category))
            ->action('View Complaint', url('/complaints/' . $this->complaint->id))
            ->line('Thank you for using our complaint management system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'complaint_number' => $this->complaint->complaint_number,
            'title' => $this->complaint->title,
            'priority' => $this->complaint->priority,
            'message' => 'New complaint created: ' . $this->complaint->complaint_number,
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }
    
}