<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a new order is created.
 *
 * @OA\Schema(
 *     schema="OrderCreatedNotification",
 *     title="OrderCreatedNotification",
 *     description="Notification sent to users when they create a new order."
 * )
 */
class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
     * Renders the email using a dedicated Blade view to ensure proper HTML rendering.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Tu orden ha sido creada!')
            ->view('emails.order_created', [
                'order' => $this->order,
                'notifiable' => $notifiable,
                'branchInfo' => $this->getBranchInfo(),
            ]);
    }

    /**
     * Get commerce branch information.
     *
     * @return string|null Branch name and address, or null if no branch is associated.
     */
    protected function getBranchInfo(): ?string
    {
        if (! $this->order->commerceBranch) {
            return null;
        }

        $branch = $this->order->commerceBranch;
        $info = $branch->name ?? $branch->commerce->name ?? 'N/A';

        if (! empty($branch->address)) {
            $info .= ' - '.$branch->address;
        }

        return $info;
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
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total_price' => $this->order->total_price,
            'status' => $this->order->status,
            'message' => 'Nueva orden creada',
        ];
    }
}
