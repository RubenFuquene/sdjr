<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome notification for new provider users.
 *
 * @OA\Schema(
 *     schema="WelcomeUserNotification",
 *     title="WelcomeUserNotification",
 *     description="Notification sent to new provider users upon registration."
 * )
 */
class WelcomeUserNotification extends Notification implements ShouldQueue
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
     * Renders a role-specific design: the provider ("aliado") gets the
     * onboarding hero, the app customer gets the sustainability-focused one.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $frontendBaseUrl = rtrim((string) config('app.frontend_prod_url'), '/');
        $isProvider = $this->userHasProviderRole();

        return (new MailMessage)
            ->subject('¡Bienvenido a Ñapa App!')
            ->view($isProvider ? 'emails.welcome-provider' : 'emails.welcome-user', [
                'notifiable' => $notifiable,
                'ctaUrl' => $frontendBaseUrl.($isProvider ? '/provider/login' : '/app/login'),
            ]);
    }

    /**
     * Whether the user has the provider role.
     *
     * Degrades gracefully to false (app-customer design) if roles cannot be
     * resolved (e.g. unsaved model in tests without a roles table).
     */
    private function userHasProviderRole(): bool
    {
        try {
            return $this->user->hasRole('provider');
        } catch (\Throwable) {
            return false;
        }
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
