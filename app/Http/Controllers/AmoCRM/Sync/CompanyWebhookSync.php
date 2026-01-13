<?php


namespace App\Http\Controllers\AmoCRM\Sync;


use App\Http\Requests\AmoCRM\Webhooks\CompanyCreatedRequest;
use App\Jobs\AmoCRM\HandleAddCompanyWebhook;
use App\Models\Account;
use App\Models\AmoCRMAccount;
use App\Models\Counterparty;
use Illuminate\Support\Arr;

class CompanyWebhookSync extends \App\Http\Controllers\Controller
{
    /**
     * @OA\Post(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/wh/add-company",
     *      operationId="wh/add-company",
     *     summary="Handle incoming company creation webhook from AmoCRM",
     *     tags={"AMOcrmClient"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON object containing account and contacts information",
     *         @OA\JsonContent(
     *             allOf={
     *                  @OA\Schema(
     *                     required={"account", "contacts"},
     *                     @OA\Property(property="account", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="account.subdomain", type="string"),
     *                          )
     *                      ),
     *                     @OA\Property(property="contacts", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="contacts.add", type="string"),
     *                          )
     *                      ),
     *                     @OA\Property(property="name", type="string", description="Company name"),
     *                     @OA\Property(property="inn", type="string", description="Taxpayer Identification Number"),
     *                     @OA\Property(property="ogrn", type="string", description="Primary State Registration Number"),
     *                     @OA\Property(property="kpp", type="string", description="Code of Reason for Registration"),
     *                     @OA\Property(property="address", type="string", description="Company address"),
     *                     @OA\Property(property="amo_company_id", type="integer", description="ID of the company in AmoCRM")
     *                 ),
     *             },
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
    
     * Create Job on process webhook
     * @param CompanyCreatedRequest $request
     */
    public function __invoke(CompanyCreatedRequest $request)
    {
        $validated = $request->validated();
        HandleAddCompanyWebhook::dispatch($validated);
    }


    /**
     * Process webhook
     * @param array $validated
     */
    public function handle(array $validated)
    {
        $amoAccount = AmoCRMAccount::whereSubdomain(Arr::get($validated, 'account.subdomain'))->first();
        if (!$amoAccount) return;

        /** @var Account $account */
        $account = $amoAccount->referenceUser()->first()->accounts()->first();
        if (!$account) return;


        $companies = Arr::get($validated, 'contacts.add');
        foreach ($companies as $company) {

            $field = $this->getLegalEntityField($company);
            if (!$field) continue;

            $inn = Arr::get($field, 'values.0.value.inn');

            if (!$inn || Counterparty::whereInn($inn)->exists())
                continue;

            $account->counterparties()->create([
                'name' => Arr::get($company, 'name'),
                'inn' => $inn,
                'ogrn' => Arr::get($field, 'values.0.value.ogrn'),
                'kpp' => Arr::get($field, 'values.0.value.kpp'),
                'address' => Arr::get($field, 'values.0.value.address'),
                'amo_company_id' => Arr::get($company, 'id')
            ]);
        }
    }

    protected function getLegalEntityField(array $company)
    {

        if (!empty($company['custom_fields'])) {
            foreach ($company['custom_fields'] as $field) {
                if (Arr::except($field, 'values.0.value.inn'))
                    return $field;
            }
        }

        return false;
    }
}
