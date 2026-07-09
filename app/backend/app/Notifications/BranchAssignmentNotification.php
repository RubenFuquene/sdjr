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
 * Notification for existing users assigned to a branch.
 *
 * Sent when an existing user (who already has a password) is assigned
 * as branch leader to a commerce branch.
 */
class BranchAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;

    protected CommerceBranch $commerceBranch;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, CommerceBranch $commerceBranch)
    {
        $this->user = $user;
        $this->commerceBranch = $commerceBranch;
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
        $dashboardUrl = rtrim($frontendBaseUrl, '/').'/dashboard';

        return (new MailMessage)
            ->subject('Asignación de sucursal - Ñapa App')
            ->greeting('Hola '.$this->user->name.',')
            ->line('Has sido asignado como líder de sucursal para: '.$this->commerceBranch->name)
            ->line('Dirección de la sucursal: '.$this->commerceBranch->address)
            ->line('Ahora tienes acceso para gestionar esta sucursal.')
            ->action('Ir al panel', $dashboardUrl)
            ->line('Si tienes alguna pregunta, contacta al dueño del comercio.')
            ->line('¡Gracias!');
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
            'message' => 'Assigned to new branch',
        ];
    }
}
