<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome notification for newly created administrator users.
 *
 * Sent when an admin creates another admin without a password. Contains a
 * password setup token so the new admin can define their own credentials.
 */
class AdminWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;

    protected string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
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
        $frontendBaseUrl = rtrim((string) config('app.frontend_prod_url'), '/');
        $url = $frontendBaseUrl.'/admin/reset-password?token='.$this->token.'&email='.urlencode($this->user->email);

        return (new MailMessage)
            ->subject('Bienvenido a Ñapa App - Configura tu contraseña')
            ->greeting('Hola '.$this->user->name.',')
            ->line('Se ha creado una cuenta de administrador para ti en Ñapa App.')
            ->action('Configurar contraseña', $url)
            ->line('Este enlace expirará en '.config('auth.passwords.users.expire').' minutos.')
            ->line('Si no esperabas esta invitación, ignora este correo.');
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
            'message' => 'Cuenta de administrador creada',
        ];
    }
}
