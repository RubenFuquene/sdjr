<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\Commerce;
use App\Models\EstablishmentType;
use App\Services\FiscalCodeResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SCRUM-362/365: matriz completa 3 tipos de establecimiento × franquicia sí/no.
 */
class FiscalCodeResolverTest extends TestCase
{
    use RefreshDatabase;

    private FiscalCodeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new FiscalCodeResolver;
    }

    private function commerceOfType(string $establishmentTypeCode, bool $operatesUnderFranchise = false): Commerce
    {
        $establishmentType = EstablishmentType::factory()->create(['code' => $establishmentTypeCode]);

        return Commerce::factory()->create([
            'establishment_type_id' => $establishmentType->id,
            'operates_under_franchise' => $operatesUnderFranchise,
        ]);
    }

    public function test_retail_never_sees_inc(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_RETAIL);

        $this->assertNotContains(FiscalCode::Inc8Prepared, $this->resolver->availableFor($commerce));
    }

    public function test_retail_sees_liquor(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_RETAIL);

        $this->assertContains(FiscalCode::Liquor, $this->resolver->availableFor($commerce));
    }

    public function test_restaurant_without_franchise_can_combine_vat_and_inc(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_RESTAURANT, false);

        $codes = $this->resolver->availableFor($commerce);

        $this->assertContains(FiscalCode::Inc8Prepared, $codes);
        $this->assertContains(FiscalCode::Vat19General, $codes);
        $this->assertContains(FiscalCode::Vat5Special, $codes);
        $this->assertContains(FiscalCode::ExcludedBasicBasket, $codes);
    }

    public function test_restaurant_with_franchise_loses_inc(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_RESTAURANT, true);

        $codes = $this->resolver->availableFor($commerce);

        $this->assertNotContains(FiscalCode::Inc8Prepared, $codes);
        $this->assertContains(FiscalCode::Vat19General, $codes);
        $this->assertContains(FiscalCode::Vat5Special, $codes);
        $this->assertContains(FiscalCode::ExcludedBasicBasket, $codes);
    }

    public function test_bakery_without_franchise_can_combine_vat_and_inc(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_BAKERY, false);

        $this->assertContains(FiscalCode::Inc8Prepared, $this->resolver->availableFor($commerce));
    }

    public function test_bakery_with_franchise_loses_inc(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_BAKERY, true);

        $this->assertNotContains(FiscalCode::Inc8Prepared, $this->resolver->availableFor($commerce));
    }

    public function test_restaurant_and_bakery_never_see_liquor(): void
    {
        foreach ([Constant::ESTABLISHMENT_TYPE_RESTAURANT, Constant::ESTABLISHMENT_TYPE_BAKERY] as $code) {
            $commerce = $this->commerceOfType($code);
            $this->assertNotContains(FiscalCode::Liquor, $this->resolver->availableFor($commerce));
        }
    }

    public function test_pending_review_is_always_available_regardless_of_type_or_franchise(): void
    {
        foreach ([Constant::ESTABLISHMENT_TYPE_RESTAURANT, Constant::ESTABLISHMENT_TYPE_BAKERY, Constant::ESTABLISHMENT_TYPE_RETAIL] as $code) {
            $establishmentType = EstablishmentType::factory()->create(['code' => $code]);

            foreach ([true, false] as $franchise) {
                $commerce = Commerce::factory()->create([
                    'establishment_type_id' => $establishmentType->id,
                    'operates_under_franchise' => $franchise,
                ]);
                $this->assertContains(FiscalCode::PendingReview, $this->resolver->availableFor($commerce));
            }
        }
    }

    public function test_is_allowed_matches_available_for(): void
    {
        $commerce = $this->commerceOfType(Constant::ESTABLISHMENT_TYPE_RETAIL);

        $this->assertTrue($this->resolver->isAllowed($commerce, FiscalCode::Liquor));
        $this->assertFalse($this->resolver->isAllowed($commerce, FiscalCode::Inc8Prepared));
    }

    public function test_unknown_establishment_type_code_yields_only_pending_review(): void
    {
        $commerce = $this->commerceOfType('XX');

        $this->assertSame([FiscalCode::PendingReview], $this->resolver->availableFor($commerce));
    }
}
