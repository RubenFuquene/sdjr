<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1\Notifications;

use App\Models\Commerce;
use App\Models\User;
use App\Notifications\CommerceRejectedNotification;
use App\Notifications\CommerceVerifiedNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WelcomeUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class NotificationQueueTest extends TestCase
{
    public function test_notifications_mail_header_uses_company_isotipo_logo(): void
    {
        $html = view('vendor.mail.html.header', [
            'url' => 'https://example.com',
        ])->render();

        $this->assertStringContainsString('/brand/isotipo-512-napa.png', $html);
    }

    public function test_welcome_provider_notification_uses_queue_and_mail_channel(): void
    {
        $user = new User([
            'name' => 'Proveedor',
            'email' => 'provider@test.com',
        ]);

        $notification = new WelcomeUserNotification($user);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame(['mail' => 'emails'], $notification->viaQueues());

        $mail = $notification->toMail($user);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('¡Bienvenido a Ñapa App!', $mail->subject);
    }

    public function test_commerce_verified_notification_uses_queue_and_mail_channel(): void
    {
        $user = new User([
            'name' => 'Comerciante',
            'email' => 'owner@test.com',
        ]);
        $commerce = new Commerce([
            'name' => 'Mi Comercio',
        ]);
        $commerce->id = 10;

        $notification = new CommerceVerifiedNotification($commerce, $commerceMessage = '¡Tu comercio ha sido verificado exitosamente!');

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame(['mail' => 'emails'], $notification->viaQueues());

        $mail = $notification->toMail($user);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('¡Tu comercio ha sido verificado!', $mail->subject);
    }

    public function test_commerce_rejected_notification_uses_queue_and_mail_channel(): void
    {
        $user = new User([
            'name' => 'Comerciante',
            'email' => 'owner@test.com',
        ]);
        $commerce = new Commerce([
            'name' => 'Mi Comercio',
        ]);
        $commerce->id = 22;

        $notification = new CommerceRejectedNotification($commerce, $commerceMessage = '¡Tu comercio ha sido rechazado!');

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame(['mail' => 'emails'], $notification->viaQueues());

        $mail = $notification->toMail($user);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('Comercio rechazado', $mail->subject);

        $arrayData = $notification->toArray($user);
        $this->assertSame(22, $arrayData['commerce_id']);
    }

    public function test_reset_password_notification_uses_queue_and_mail_channel(): void
    {
        $user = new User([
            'name' => 'Usuario',
            'email' => 'user@test.com',
        ]);
        $token = 'test-token';

        $notification = new ResetPasswordNotification($token, $user->email);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame(['mail' => 'emails'], $notification->viaQueues());

        $mail = $notification->toMail($user);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('Restablecimiento de contraseña', $mail->subject);
    }
}
