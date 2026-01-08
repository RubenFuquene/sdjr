<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
use App\Models\Commerce;
use Illuminate\Support\Facades\DB;
use App\Services\CommerceDocumentService;
use App\Services\LegalRepresentativeService;

class CommerceBasicDataService
{
    /**
     * LegalRepresentativeService instance.
     */
    private LegalRepresentativeService $legalRepresentativeService;

    /**
     * CommerceDocumentService instance.
     */
    private CommerceDocumentService $commerceDocumentService;

    /**
     * CommercePayoutMethodService instance.
     */
    private CommercePayoutMethodService $myAccountService;

    /**
     * Constructor to initialize services.
     */
    public function __construct()
    {
        $this->legalRepresentativeService = new LegalRepresentativeService();
        $this->commerceDocumentService = new CommerceDocumentService();
        $this->myAccountService = new CommercePayoutMethodService();
    }

    /**
     * Store commerce with related legal representatives and documents in a transaction.
     *
     * @throws Throwable
     */
    public function store(array $data): Commerce
    {
        return DB::transaction(function () use ($data) {
            $commerceData = $data['commerce'];
            $commerce = Commerce::create($commerceData);

            if (! empty($data['legal_representatives'])) {
                $legalRepresentativeData = $data['legal_representatives'];
                $legalRepresentativeData['commerce_id'] = $commerce->id;
                $this->legalRepresentativeService->store($legalRepresentativeData);
            }

            if (! empty($data['commerce_documents'])) {
                $commerceDocumentsData = $data['commerce_documents'];
                $commerceDocumentsData['commerce_id'] = $commerce->id;
                $this->commerceDocumentService->store($commerceDocumentsData);
            }

            if( ! empty($data['my_account'])){
                $myAccountData = $data['my_account'];
                $myAccountData['commerce_id'] = $commerce->id;
                $this->myAccountService->store($myAccountData);
            }

            return $commerce->load(['legalRepresentatives', 'commerceDocuments', 'myAccount']);
        });
    }
}
