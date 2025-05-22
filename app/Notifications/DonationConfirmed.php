<?php

namespace App\Notifications;

use App\Models\DonationTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public DonationTransaction $transaction) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  \App\Models\User  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Donation Confirmed!')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your donation of {$this->transaction->amount} {$this->transaction->currency} 
                to {$this->transaction->donation?->campaign?->title} has been confirmed")
            ->line('Thank you for your support!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'donation_transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'campaign_name' => $this->transaction->donation?->campaign?->title,
        ];
    }
}
