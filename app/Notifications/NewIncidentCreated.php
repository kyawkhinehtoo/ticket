<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class NewIncidentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Incident $incident)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $smtpSetting = SmtpSetting::where('is_active', true)->first();

        if ($smtpSetting) {
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $smtpSetting->host,
                'port' => $smtpSetting->port,
                'encryption' => $smtpSetting->encryption,
                'username' => $smtpSetting->username,
                'password' => $smtpSetting->password,
                'timeout' => 30,
                'auth_mode' => null,
            ]);

            Config::set('mail.from', [
                'address' => $smtpSetting->from_address,
                'name' => $smtpSetting->from_name,
            ]);
        }

        return (new MailMessage)
            ->subject('New Incident Created: ' . $this->incident->title)
            ->line('A new incident has been created by a company.')
            ->line('Incident Details:')
            ->line('Type: ' . $this->incident->type)
            ->line('Description: ' . $this->incident->description)
            ->action('View Incident', url('/admin/incidents/' . $this->incident->id))
            ->line('Thank you for using our application!');
    }
}