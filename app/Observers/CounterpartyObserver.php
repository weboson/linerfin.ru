<?php

namespace App\Observers;

use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\CustomFieldsValues\LegalEntityCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\LegalEntityCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\LegalEntityCustomFieldValueModel;
use App\Http\Controllers\AmoCRM\AmoCRMProvider;
use App\Http\Controllers\AmoCRM\Sync\CompaniesSync;
use App\Models\AmoCRMAccount;
use App\Models\Counterparty;
use App\Models\User;

class CounterpartyObserver
{
    /**
     * Handle the Counterparty "created" event.
     *
     * @param  \App\Models\Counterparty  $counterparty
     * @return void
     */
    public function created(Counterparty $counterparty)
    {
        /** @var User $user */
        $user = $counterparty->account->user;
        if($user){

            try{
                /** @var AmoCRMAccount $account */
                $account = $user->amoCrmAccounts()->first();
                if($account) {
                    $client = AmoCRMProvider::initClient($account);
                    $field = CompaniesSync::getVatField($client);

                    $amoCompaniesCollection = new CompaniesCollection();
                    $amoCompany = new CompanyModel();
                    $amoCompany->setName($counterparty->name);

                    $customFieldsCollection = new CustomFieldsValuesCollection();

                    $legalEntityFieldValues = new LegalEntityCustomFieldValuesModel();
                    $legalEntityFieldValues->setFieldId($field->getId());
                    $legalEntityFieldValues->setValues(
                        (new LegalEntityCustomFieldValueCollection)
                            ->add(
                                (new LegalEntityCustomFieldValueModel)
                                    ->setName($counterparty->name)
                                    ->setVatId($counterparty->inn)
                                    ->setTaxRegistrationReasonCode($counterparty->ogrn)
                                    ->setAddress($counterparty->legal_address)
                                    ->setKpp($counterparty->kpp)
                            )
                    );

                    $customFieldsCollection->add($legalEntityFieldValues);
                    $amoCompany->setCustomFieldsValues($customFieldsCollection);
                    $amoCompaniesCollection->add($amoCompany);

                    $createdCollection = $client->companies()->add($amoCompaniesCollection);

                    $createdCompany = $createdCollection->first();
                    if($createdCompany){
                        $counterparty->update([
                            'amo_company_id' => $createdCompany->getId()
                        ]);
                    }
                }
            }
            catch (\Exception $e){

            }
        }
    }

    /**
     * Handle the Counterparty "updated" event.
     *
     * @param  \App\Models\Counterparty  $counterparty
     * @return void
     */
    public function updated(Counterparty $counterparty)
    {
        //
    }

    /**
     * Handle the Counterparty "deleted" event.
     *
     * @param  \App\Models\Counterparty  $counterparty
     * @return void
     */
    public function deleted(Counterparty $counterparty)
    {
        //
    }

    /**
     * Handle the Counterparty "restored" event.
     *
     * @param  \App\Models\Counterparty  $counterparty
     * @return void
     */
    public function restored(Counterparty $counterparty)
    {
        //
    }

    /**
     * Handle the Counterparty "force deleted" event.
     *
     * @param  \App\Models\Counterparty  $counterparty
     * @return void
     */
    public function forceDeleted(Counterparty $counterparty)
    {
        //
    }
}
