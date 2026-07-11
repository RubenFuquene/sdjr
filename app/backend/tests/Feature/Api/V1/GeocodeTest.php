<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    use RefreshDatabase;

    private const SEARCH_RESPONSE = [
        [
            'place_id' => 12345,
            'lat' => '4.598100',
            'lon' => '-74.076100',
            'display_name' => 'Calle 10 #5-20, La Candelaria, Bogotá, Colombia',
            'address' => [
                'road' => 'Calle 10',
                'house_number' => '5-20',
                'suburb' => 'La Candelaria',
                'city' => 'Bogotá',
                'state' => 'Bogotá D.C.',
                'country' => 'Colombia',
            ],
        ],
    ];

    private const REVERSE_RESPONSE = [
        'place_id' => 12345,
        'lat' => '4.598100',
        'lon' => '-74.076100',
        'display_name' => 'Calle 10 #5-20, La Candelaria, Bogotá, Colombia',
        'address' => [
            'road' => 'Calle 10',
            'house_number' => '5-20',
            'neighbourhood' => 'La Candelaria',
            'city' => 'Bogotá',
            'state' => 'Bogotá D.C.',
            'country' => 'Colombia',
        ],
    ];

    /**
     * Nunca debe golpearse el servicio real de Nominatim en tests: se fakea la respuesta HTTP.
     */
    public function test_search_geocodes_address_via_nominatim_proxy(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response(self::SEARCH_RESPONSE, 200),
        ]);

        Permission::firstOrCreate(['name' => 'provider.geocode.search', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.search');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode?q=Calle 10, La Candelaria, Bogotá');

        $response->assertStatus(200)
            ->assertJsonPath('data.lat', 4.5981)
            ->assertJsonPath('data.lng', -74.0761)
            ->assertJsonPath('data.address.road', 'Calle 10')
            ->assertJsonPath('data.address.house_number', '5-20')
            ->assertJsonPath('data.address.neighborhood', 'La Candelaria')
            ->assertJsonPath('data.address.city', 'Bogotá');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'nominatim.openstreetmap.org/search')
                && $request->hasHeader('User-Agent')
                && str_contains($request->url(), 'countrycodes=co');
        });
    }

    public function test_search_returns_404_when_no_results(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([], 200),
        ]);

        Permission::firstOrCreate(['name' => 'provider.geocode.search', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.search');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode?q=direccion inexistente xyz');

        $response->assertStatus(404);
    }

    public function test_search_requires_q_parameter(): void
    {
        Permission::firstOrCreate(['name' => 'provider.geocode.search', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.search');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode');

        $response->assertStatus(422);
    }

    public function test_search_caches_repeated_queries_without_extra_requests(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response(self::SEARCH_RESPONSE, 200),
        ]);

        Permission::firstOrCreate(['name' => 'provider.geocode.search', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.search');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/geocode?q=Calle 10, La Candelaria, Bogotá')->assertStatus(200);
        $this->getJson('/api/v1/geocode?q=Calle 10, La Candelaria, Bogotá')->assertStatus(200);

        Http::assertSentCount(1);
    }

    public function test_reverse_geocodes_coordinates_via_nominatim_proxy(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response(self::REVERSE_RESPONSE, 200),
        ]);

        Permission::firstOrCreate(['name' => 'provider.geocode.reverse', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.reverse');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode/reverse?lat=4.5981&lng=-74.0761');

        $response->assertStatus(200)
            ->assertJsonPath('data.address.road', 'Calle 10')
            ->assertJsonPath('data.address.house_number', '5-20')
            ->assertJsonPath('data.address.neighborhood', 'La Candelaria')
            ->assertJsonPath('data.address.city', 'Bogotá');
    }

    /**
     * OSM mete artefactos administrativos (UPZ, Localidad) en los campos
     * neighbourhood/suburb cuando no tiene un barrio real mapeado. No son
     * barrios: deben descartarse y devolver neighborhood null.
     */
    public function test_reverse_discards_administrative_placeholders_as_neighborhood(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'lat' => '4.665749',
                'lon' => '-74.053475',
                'display_name' => '11-37, Calle 82, UPZs de Bogotá, Localidad Chapinero, Bogotá, Colombia',
                'address' => [
                    'road' => 'Calle 82',
                    'house_number' => '11-37',
                    'neighbourhood' => 'UPZs de Bogotá',
                    'suburb' => 'Localidad Chapinero',
                    'city' => 'Bogotá',
                    'state' => 'Bogotá, Distrito Capital',
                ],
            ], 200),
        ]);

        Permission::firstOrCreate(['name' => 'provider.geocode.reverse', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.reverse');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode/reverse?lat=4.665749&lng=-74.053475');

        $response->assertStatus(200)
            ->assertJsonPath('data.address.road', 'Calle 82')
            ->assertJsonPath('data.address.neighborhood', null)
            ->assertJsonPath('data.address.city', 'Bogotá');
    }

    public function test_reverse_requires_valid_lat_lng(): void
    {
        Permission::firstOrCreate(['name' => 'provider.geocode.reverse', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.geocode.reverse');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode/reverse?lat=200&lng=-74.0761');

        $response->assertStatus(422);
    }

    public function test_search_forbidden_without_permission(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/geocode?q=Calle 10, La Candelaria, Bogotá');

        $response->assertStatus(403);
    }

    public function test_search_unauthenticated_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/geocode?q=Calle 10, La Candelaria, Bogotá');

        $response->assertStatus(401);
    }
}
