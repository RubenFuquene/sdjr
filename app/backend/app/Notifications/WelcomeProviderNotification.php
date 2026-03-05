<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome notification for new provider users.
 *
 * @OA\Schema(
 *     schema="WelcomeProviderNotification",
 *     title="WelcomeProviderNotification",
 *     description="Notification sent to new provider users upon registration."
 * )
 */
class WelcomeProviderNotification extends Notification
{
    use Queueable;

    protected User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
            ->subject('¡Bienvenido a Ñapa App!')
            ->greeting('Hola '.$this->user->name.',')
            ->line('Tu registro como proveedor ha sido exitoso.')
            ->line('Ya puedes acceder a tu cuenta y comenzar a utilizar nuestros servicios.')
            ->action('Ir al panel', url('/provider/dashboard'))
            ->line('¡Gracias por confiar en nosotros!');
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
            'user_id' => $this->user->id,
            'message' => 'Bienvenido a la plataforma de proveedores',
        ];
    }
}
