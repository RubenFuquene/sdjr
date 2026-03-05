<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Commerce;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a commerce is rejected.
 *
 * @OA\Schema(
 *     schema="CommerceRejectedNotification",
 *     title="CommerceRejectedNotification",
 *     description="Notification sent to the owner when a commerce is rejected."
 * )
 */
class CommerceRejectedNotification extends Notification
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
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Comercio rechazado')
            ->greeting('Hola '.($notifiable->name ?? 'Usuario').',')
            ->line('Lamentamos informarte que tu comercio "'.$this->commerce->name.'" ha sido rechazado tras el proceso de verificación.')
            ->line('Por favor revisa la información registrada y vuelve a intentarlo o contacta soporte para más detalles.')
            ->action('Ver detalles', url('/provider/dashboard'))
            ->line('Gracias por tu interés en nuestra plataforma.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'commerce_id' => $this->commerce->id,
            'message' => 'Tu comercio ha sido rechazado',
        ];
    }
}
