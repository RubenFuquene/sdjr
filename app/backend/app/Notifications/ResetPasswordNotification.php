<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class ResetPasswordNotification
 *
 * Sends a password reset email to the user.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $token;

    protected string $email;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
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
        $frontendBaseUrl = rtrim((string) config('app.frontend_prod_url'), '/');
        $url = $frontendBaseUrl.$this->resolveResetPath($notifiable)
            .'?token='.$this->token.'&email='.urlencode($this->email);

        return (new MailMessage)
            ->subject('Restablecimiento de contraseña')
            ->view('emails.reset-password', [
                'url' => $url,
                'expireMinutes' => (int) config('auth.passwords.users.expire'),
            ]);
    }

    /**
     * Resolve the frontend reset path for the notifiable based on its role,
     * so the email link lands on the module matching the user (admin/app/provider).
     *
     * @param  mixed  $notifiable
     */
    private function resolveResetPath($notifiable): string
    {
        // Degrade gracefully to the global reset page if roles cannot be
        // resolved (e.g. relation/table unavailable): it works for any role.
        try {
            $roles = method_exists($notifiable, 'getRoleNames')
                ? $notifiable->getRoleNames()->all()
                : [];
        } catch (\Throwable) {
            return '/reset-password';
        }

        return match (true) {
            (bool) array_intersect(['admin', 'superadmin'], $roles) => '/admin/reset-password',
            (bool) array_intersect(['provider', 'branch_leader'], $roles) => '/provider/reset-password',
            in_array('user', $roles, true) => '/app/reset-password',
            default => '/reset-password',
        };
    }
}
