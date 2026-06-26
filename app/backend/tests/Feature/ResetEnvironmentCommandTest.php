<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResetEnvironmentCommandTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('productionLikeEnvironments')]
    public function test_command_is_blocked_in_production_like_environments(string $env): void
    {
        $this->app['env'] = $env;

        $this->artisan('app:reset-environment', ['--force' => true])
            ->assertExitCode(1);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function productionLikeEnvironments(): array
    {
        return [
            'production' => ['production'],
            'prod' => ['prod'],
            'prd' => ['prd'],
        ];
    }
}
