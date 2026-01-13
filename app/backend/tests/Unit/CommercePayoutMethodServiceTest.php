<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CommercePayoutMethod;
use App\Services\CommercePayoutMethodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercePayoutMethodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_method(): void
    {
        $service = new CommercePayoutMethodService();
        $data = CommercePayoutMethod::factory()->make()->toArray();
        $data['commerce_id'] = 1;
        $data['owner_id'] = 1;
        $method = $service->store($data);
        $this->assertInstanceOf(CommercePayoutMethod::class, $method);
        $this->assertDatabaseHas('commerce_payout_methods', ['id' => $method->id]);
    }

    public function test_update_method(): void
    {
        $service = new CommercePayoutMethodService();
        $method = CommercePayoutMethod::factory()->create();
        $updated = $service->update($method, ['account_number' => '123456789']);
        $this->assertEquals('123456789', $updated->account_number);
    }

    public function test_delete_method(): void
    {
        $service = new CommercePayoutMethodService();
        $method = CommercePayoutMethod::factory()->create();
        $result = $service->delete($method);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('commerce_payout_methods', ['id' => $method->id]);
    }
}
