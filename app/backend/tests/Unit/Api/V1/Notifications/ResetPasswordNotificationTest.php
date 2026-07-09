<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1\Notifications;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    /**
     * Build a lightweight notifiable exposing the given Spatie role names,
     * without touching the database.
     *
     * @param  array<int, string>  $roles
     */
    private function notifiableWithRoles(array $roles): object
    {
        return new class($roles)
        {
            /** @param array<int, string> $roles */
            public function __construct(private array $roles) {}

            public function getRoleNames(): Collection
            {
                return collect($this->roles);
            }
        };
    }

    public function test_reset_link_targets_module_by_role(): void
    {
        $cases = [
            [['admin'], '/admin/reset-password?'],
            [['superadmin'], '/admin/reset-password?'],
            [['provider'], '/provider/reset-password?'],
            [['branch_leader'], '/provider/reset-password?'],
            [['user'], '/app/reset-password?'],
            [[], '/reset-password?'],
        ];

        foreach ($cases as [$roles, $expectedPath]) {
            $mail = (new ResetPasswordNotification('tok123', 'user@test.com'))
                ->toMail($this->notifiableWithRoles($roles));

            $this->assertInstanceOf(MailMessage::class, $mail);
            $url = (string) $mail->viewData['url'];
            $this->assertStringContainsString(
                $expectedPath,
                $url,
                'Roles ['.implode(',', $roles).'] should target '.$expectedPath
            );
            $this->assertStringContainsString('token=tok123', $url);
            $this->assertStringContainsString('email=user%40test.com', $url);
        }
    }

    public function test_admin_precedence_over_provider_and_user(): void
    {
        $notifiable = $this->notifiableWithRoles(['user', 'provider', 'admin']);

        $mail = (new ResetPasswordNotification('t', 'a@b.com'))->toMail($notifiable);

        $this->assertStringContainsString('/admin/reset-password?', (string) $mail->viewData['url']);
    }
}
