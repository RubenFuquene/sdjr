<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\LegalRepresentative;
use App\Services\LegalRepresentativeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalRepresentativeServiceTest extends TestCase
{
    use RefreshDatabase;

    private LegalRepresentativeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LegalRepresentativeService();
    }

    public function test_store_and_show_legal_representative(): void
    {
        $data = LegalRepresentative::factory()->make()->toArray();
        $legal = $this->service->store($data);
        $this->assertDatabaseHas('legal_representatives', ['id' => $legal->id]);
        $found = $this->service->show($legal->id);
        $this->assertEquals($legal->id, $found->id);
    }

    public function test_update_legal_representative(): void
    {
        $legal = LegalRepresentative::factory()->create();
        $update = [
            'name' => 'Nuevo Nombre',
            'last_name' => 'Nuevo Apellido',
            'document' => '9999999999',
            'document_type' => 'NIT',
            'commerce_id' => $legal->commerce_id,
        ];
        $updated = $this->service->update($legal->id, $update);
        $this->assertEquals('Nuevo Nombre', $updated->name);
        $this->assertEquals('Nuevo Apellido', $updated->last_name);
        $this->assertEquals('9999999999', $updated->document);
        $this->assertEquals('NIT', $updated->document_type);
    }

    public function test_destroy_legal_representative(): void
    {
        $legal = LegalRepresentative::factory()->create();
        $this->service->destroy($legal->id);
        $this->assertSoftDeleted('legal_representatives', ['id' => $legal->id]);
    }
}
