<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use App\Models\Bank;
use App\Models\Commerce;
use App\Models\CommercePayoutMethod;
use App\Models\User;
use App\Services\CommercePayoutMethodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercePayoutMethodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_method(): void
    {
        $service = new CommercePayoutMethodService;
        $commerce = Commerce::factory()->create();
        $bank = Bank::factory()->create();
        $owner = User::factory()->create();

        $data = CommercePayoutMethod::factory()->make()->toArray();
        $data['commerce_id'] = $commerce->id;
        $data['bank_id'] = $bank->id;
        $data['owner_id'] = $owner->id;

        $method = $service->store($data);

        $this->assertInstanceOf(CommercePayoutMethod::class, $method);
        $this->assertDatabaseHas('commerce_payout_methods', ['id' => $method->id]);
    }

    public function test_update_method(): void
    {
        $service = new CommercePayoutMethodService;
        $method = CommercePayoutMethod::factory()->create();
        $updated = $service->update($method, ['account_number' => '123456789']);
        $this->assertEquals('123456789', $updated->account_number);
    }

    public function test_delete_method(): void
    {
        $service = new CommercePayoutMethodService;
        $method = CommercePayoutMethod::factory()->create();
        $result = $service->delete($method);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('commerce_payout_methods', ['id' => $method->id]);
    }
}
