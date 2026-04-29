<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Commerce;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a commerce is verified and activated.
 */
class CommerceVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Commerce $commerce;

    /**
     * Create a new notification instance.
     */
    public function __construct(Commerce $commerce)
    {
        $this->commerce = $commerce;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Define queue names per channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $frontendBaseUrl = (string) config('app.frontend_prod_url');
        $commerceUrl = rtrim($frontendBaseUrl, '/').'/provider/dashboard';

        return (new MailMessage)
            ->subject('¡Tu comercio ha sido verificado!')
            ->greeting('Hola '.$notifiable->name)
            ->line('Nos complace informarte que tu comercio "'.$this->commerce->name.'" ha sido verificado y ahora se encuentra activo en la plataforma.')
            ->line('Ya puedes acceder a todas las funcionalidades disponibles para comercios verificados.')
            ->action('Acceder a tu comercio', $commerceUrl)
            ->line('Gracias por confiar en nosotros.');
    }
}
