<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use App\Models\CommerceDocument;
use App\Models\LegalRepresentative;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommerceBasicDataService
{
    /**
     * Store commerce with related legal representatives and documents in a transaction.
     *
     * @param array $data
     * @return Commerce
     * @throws Throwable
     */
    public function store(array $data): Commerce
    {
        return DB::transaction(function () use ($data) {
            $commerceData = $data['commerce'];
            $commerce = Commerce::create($commerceData);

            if (!empty($data['legal_representatives'])) {
                foreach ($data['legal_representatives'] as $lr) {
                    $lr['commerce_id'] = $commerce->id;
                    LegalRepresentative::create($lr);
                }
            }

            if (!empty($data['commerce_documents'])) {
                foreach ($data['commerce_documents'] as $doc) {
                    $doc['commerce_id'] = $commerce->id;
                    CommerceDocument::create($doc);
                }
            }

            return $commerce->load(['legalRepresentatives', 'legalRepresentatives.commerce']);
        });
    }
}
