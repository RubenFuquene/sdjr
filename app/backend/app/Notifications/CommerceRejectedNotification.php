<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Commerce;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
class CommerceRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Commerce $commerce;

    protected string $customMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Commerce $commerce, string $customMessage)
    {
        $this->commerce = $commerce;
        $this->customMessage = $customMessage;
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

        return (new MailMessage)
            ->subject('Comercio rechazado')
            ->view('emails.commerce-rejected', [
                'notifiable' => $notifiable,
                'commerce' => $this->commerce,
                'customMessage' => $this->customMessage,
                'ctaUrl' => $frontendBaseUrl.'/provider/login',
            ]);
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
