<?php

namespace App\Http\Controllers\AmoCRM\Sync;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\EntitiesServices\Companies;
use AmoCRM\EntitiesServices\CustomFields;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\CustomFields\LegalEntityCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\LegalEntityCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\LegalEntityCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\LegalEntityCustomFieldValueModel;
use App\Http\Controllers\AmoCRM\AmoCRMProvider;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponses;
use App\Models\AmoCRMAccount;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompaniesSync extends Controller
{

    use JsonResponses;

    protected $apiClient;

    /** @var AmoCRMAccount */
    protected $accountModel;
    protected $user;

    protected $account;

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {

            /** @var User $user */
            $this->user = Auth::user();
            $this->account = $this->user->accounts()->first();
            $this->accountModel = $this->user->amoCrmAccounts()->first();


            $this->apiClient = AmoCRMProvider::initClient($this->accountModel);

            return $next($request);
        });
    }


    /**
     * Get Legal Entity field or create new
     * @return \AmoCRM\Models\BaseApiModel|\AmoCRM\Models\CustomFields\CustomFieldModel|LegalEntityCustomFieldModel|\AmoCRM\Models\CustomFields\WithEnumCustomFieldModel|mixed|null
     * @throws AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    public static function getVatField(AmoCRMApiClient $client)
    {
        /** @var CustomFields $customFieldsService */
        $customFieldsService = $client->customFields(EntityTypesInterface::COMPANIES);
        $customFieldsCollection = $customFieldsService->get();

        foreach ($customFieldsCollection as $field) {
            if ($field instanceof LegalEntityCustomFieldModel)
                return $field;
        }

        $field = new LegalEntityCustomFieldModel();
        $field->setName('Юр. лицо');
        $field->setCode('LEGAL_ENTITY_FIELD_CUSTOM');

        $customFieldsCollection->add($field);
        $customFieldsCollection = $customFieldsService->add($customFieldsCollection);

        $field = $customFieldsCollection->getBy('code', 'LEGAL_ENTITY_FIELD_CUSTOM');

        return $field;
    }


    protected function getCompanyVat(LegalEntityCustomFieldValuesModel $legalEntityField)
    {
        $legalEntityValues = $legalEntityField->getValues();
        $legalEntityValue = $legalEntityValues->first()->getValue();

        return isset($legalEntityValue['vat_id']) ? $legalEntityValue['vat_id'] : null;
    }


    public function sync()
    {

        $expectCompanies = [];

        $field = self::getVatField($this->apiClient);
        if (!$field) return $this->error([], 'Не удалось получить поле Юр. лица');

        /** @var Companies $service */
        $service = $this->apiClient->companies();

        try{
            /** @var CompaniesCollection $companies */
            $companies = $service->get();

            $pageLimit = 1000000;
            $page = 0;
            while ($page < $pageLimit) {

                $page++;

                /** @var CompanyModel $company */
                foreach ($companies as $company) {

                    if (!$customFields = $company->getCustomFieldsValues())
                        continue; // skip if CF not defined

                    // get legal entity
                    /** @var LegalEntityCustomFieldValuesModel $legalEntityField */
                    $legalEntityField = $customFields->getBy('field_id', $field->getId());
                    if (!$legalEntityField)
                        continue; // skip if legal entity field not defined

                    $legalEntityValues = $legalEntityField->getValues();
                    $amoCompany = $legalEntityValues->first()->getValue();
                    if (!$amoCompany['vat_id']) continue; // skip if VAT id not defined
                    $vatId = $amoCompany['vat_id'];


                    // find counterparty or create new
                    if (Counterparty::whereInn($vatId)->count()) {
                        $expectCompanies = array_merge($expectCompanies, Counterparty::whereInn($vatId)->pluck('id')->toArray());
                        continue;
                    }

                    // or create new
                    $localCompany = $this->account->counterparties()->create([
                        'name' => $amoCompany['name'],
                        'kpp' => $amoCompany['kpp'],
                        'ogrn' => $amoCompany['tax_registration_reason_code'],
                        'legal_address' => $amoCompany['address'],
                        'inn' => $vatId,
                        'amo_company_id' => $company->getId()
                    ]);

                    $expectCompanies[] = $localCompany->id;
                }

                if ($companies->getNextPageLink())
                    $service->nextPage($companies);
                else
                    break; // end
            }
        }
        catch (AmoCRMApiNoContentException $e){
            // it's ok
        }
        catch (\Exception $e){
            Log::error($e);
            return $this->error();
        }


        // STEP 2: sync companies to amoCRM
        $localCompanies = $this->account->counterparties()->whereNotNull('inn');
        if(count($expectCompanies)) {
            $localCompanies->whereNotIn('id', array_unique($expectCompanies));
        }


        $localCompanies->chunk(50, function (Collection $companies) use ($service, $field) {
            $amoCompaniesCollection = new CompaniesCollection();

            foreach ($companies as $localCompany) {

                $amoCompany = new CompanyModel();
                $amoCompany->setName($localCompany->name);

                $customFieldsCollection = new CustomFieldsValuesCollection();

                $legalEntityFieldValues = new LegalEntityCustomFieldValuesModel();
                $legalEntityFieldValues->setFieldId($field->getId());
                $legalEntityFieldValues->setValues(
                    (new LegalEntityCustomFieldValueCollection)
                        ->add(
                            (new LegalEntityCustomFieldValueModel)
                                ->setName($localCompany->name)
                                ->setVatId($localCompany->inn)
                                ->setTaxRegistrationReasonCode($localCompany->ogrn)
                                ->setAddress($localCompany->legal_address)
                                ->setKpp($localCompany->kpp)
                        )
                );

                $customFieldsCollection->add($legalEntityFieldValues);
                $amoCompany->setCustomFieldsValues($customFieldsCollection);

                $amoCompaniesCollection->add($amoCompany);
            }

            $createdCollection = $service->add($amoCompaniesCollection);

            // update reference
            foreach($createdCollection as $key => $createdCompany){
                $companies[$key]->update([
                    'amo_company_id' => $createdCompany->getId()
                ]);
            }
        });


        return $this->success();
    }

}
