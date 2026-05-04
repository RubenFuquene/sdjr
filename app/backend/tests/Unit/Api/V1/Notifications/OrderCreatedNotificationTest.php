<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1\Notifications;

use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class OrderCreatedNotificationTest extends TestCase
{
    public function test_order_created_notification_uses_queue_and_mail_channel(): void
    {
        $order = new Order([
            'id' => 1,
            'user_id' => 1,
            'commerce_branch_id' => 1,
            'total_price' => 100.00,
            'status' => 'pending',
        ]);
        $order->id = 1;

        $user = new User([
            'name' => 'Cliente',
            'email' => 'cliente@test.com',
        ]);

        $notification = new OrderCreatedNotification($order);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame(['mail' => 'emails'], $notification->viaQueues());
    }

    public function test_order_created_notification_mail_content(): void
    {
        $user = new User([
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
        ]);

        $branch = new CommerceBranch([
            'name' => 'Sucursal Centro',
        ]);
        $branch->id = 1;

        $order = new Order([
            'id' => 123,
            'user_id' => 1,
            'commerce_branch_id' => 1,
            'total_price' => 150.50,
            'status' => 'pending',
        ]);
        $order->id = 123;
        $order->setRelation('commerceBranch', $branch);

        $product = new Product([
            'title' => 'Producto Test',
        ]);

        $item = new OrderItem([
            'order_id' => 123,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 75.25,
        ]);
        $item->setRelation('product', $product);

        $order->setRelation('items', collect([$item]));

        $notification = new OrderCreatedNotification($order);

        $mail = $notification->toMail($user);

        // Subject and type
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('¡Tu orden ha sido creada!', $mail->subject);

        // Uses a Blade view (not the fluent ->line() API)
        $this->assertSame('emails.order_created', $mail->view);

        // View data contains the required variables
        $this->assertArrayHasKey('order', $mail->viewData);
        $this->assertArrayHasKey('notifiable', $mail->viewData);
        $this->assertArrayHasKey('branchInfo', $mail->viewData);

        // View data values are correct
        $this->assertSame($order, $mail->viewData['order']);
        $this->assertSame($user, $mail->viewData['notifiable']);
        $this->assertSame('Sucursal centro', $mail->viewData['branchInfo']);

        // Rendered HTML contains expected content
        $html = view($mail->view, $mail->viewData)->render();
        $this->assertStringContainsString('Cliente Test', $html);
        $this->assertStringContainsString('#123', $html);
        $this->assertStringContainsString('Producto Test', $html);
        $this->assertStringContainsString('150.50', $html);
        $this->assertStringContainsString('Sucursal centro', $html);
        $this->assertStringContainsString('pending', $html);
        $this->assertStringContainsString('isotipo-512-napa.png', $html);
    }

    public function test_order_created_notification_to_array(): void
    {
        $order = new Order([
            'id' => 456,
            'user_id' => 2,
            'commerce_branch_id' => 1,
            'total_price' => 200.00,
            'status' => 'pending',
        ]);
        $order->id = 456;

        $user = new User([
            'name' => 'Usuario',
            'email' => 'usuario@test.com',
        ]);

        $notification = new OrderCreatedNotification($order);

        $arrayData = $notification->toArray($user);

        $this->assertSame(456, $arrayData['order_id']);
        $this->assertSame(2, $arrayData['user_id']);
        $this->assertSame(200.00, $arrayData['total_price']);
        $this->assertSame('pending', $arrayData['status']);
        $this->assertSame('Nueva orden creada', $arrayData['message']);
    }
}
