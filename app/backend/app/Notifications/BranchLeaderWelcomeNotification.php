<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\CommerceBranch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome notification for new branch leader users.
 *
 * Sent when a new user is created without password and assigned as branch leader.
 * Contains a password setup token for initial authentication.
 */
class BranchLeaderWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;

    protected CommerceBranch $commerceBranch;

    protected string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, CommerceBranch $commerceBranch, string $token)
    {
        $this->user = $user;
        $this->commerceBranch = $commerceBranch;
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
        $frontendBaseUrl = (string) config('app.frontend_prod_url');
        $url = rtrim($frontendBaseUrl, '/').'/newPassword?token='.$this->token.'&email='.urlencode($this->user->email);

        return (new MailMessage)
            ->subject('Bienvenido a Ñapa App - Configura tu contraseña')
            ->greeting('Hola '.$this->user->name.',')
            ->line('Has sido asignado como líder de sucursal para: '.$this->commerceBranch->name)
            ->line('Para comenzar, configura tu contraseña haciendo clic en el botón de abajo:')
            ->action('Configurar contraseña', $url)
            ->line('Este enlace expirará en '.config('auth.passwords.users.expire').' minutos.')
            ->line('Si no esperabas esta invitación, contacta al dueño del comercio.')
            ->line('¡Gracias por unirte a nuestro equipo!');
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
            'branch_id' => $this->commerceBranch->id,
            'commerce_id' => $this->commerceBranch->commerce_id,
            'message' => 'Assigned as Branch Leader',
        ];
    }
}
