<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CountryRequest;
use App\Http\Resources\Api\V1\CountryResource;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="SDJR API",
 *      description="API documentation for SDJR application",
 *      @OA\Contact(
 *          email="admin@sdjr.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Tag(
 *     name="Countries",
 *     description="API Endpoints of Countries"
 * )
 */
class CountryController extends Controller
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/countries",
     *      operationId="getCountriesList",
     *      tags={"Countries"},
     *      summary="Get list of countries",
     *      description="Returns list of countries",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CountryResource")
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $countries = $this->countryService->getPaginated();
        return CountryResource::collection($countries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/countries",
     *      operationId="storeCountry",
     *      tags={"Countries"},
     *      summary="Store new country",
     *      description="Returns country data",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CountryRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CountryResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     *
     * @param CountryRequest $request
     * @return CountryResource
     */
    public function store(CountryRequest $request): CountryResource
    {
        $country = $this->countryService->create($request->validated());
        return new CountryResource($country);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/countries/{id}",
     *      operationId="getCountryById",
     *      tags={"Countries"},
     *      summary="Get country information",
     *      description="Returns country data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Country id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CountryResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     *
     * @param string $id
     * @return CountryResource|JsonResponse
     */
    public function show(string $id): CountryResource|JsonResponse
    {
        $country = $this->countryService->find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], 404);
        }

        return new CountryResource($country);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/countries/{id}",
     *      operationId="updateCountry",
     *      tags={"Countries"},
     *      summary="Update existing country",
     *      description="Returns updated country data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Country id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CountryRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CountryResource")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     *
     * @param CountryRequest $request
     * @param string $id
     * @return CountryResource|JsonResponse
     */
    public function update(CountryRequest $request, string $id): CountryResource|JsonResponse
    {
        $country = $this->countryService->find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], 404);
        }

        $updatedCountry = $this->countryService->update($country, $request->validated());
        return new CountryResource($updatedCountry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/countries/{id}",
     *      operationId="deleteCountry",
     *      tags={"Countries"},
     *      summary="Delete existing country",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Country id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Country deleted successfully")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $country = $this->countryService->find($id);

        if (!$country) {
            return response()->json(['message' => 'Country not found'], 404);
        }

        $this->countryService->delete($country);
        return response()->json(['message' => 'Country deleted successfully']);
    }
}
