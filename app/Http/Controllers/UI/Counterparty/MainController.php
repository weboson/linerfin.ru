<?php

namespace App\Http\Controllers\UI\Counterparty;

use App\Http\Abstraction\AccountAbstract;
use App\Http\Traits\JsonResponses;
use App\Models\Bank;
use App\Models\Contact;
use App\Models\Counterparty;
use App\Models\CounterpartyAccount;
use App\Models\CounterpartyCategory;
use App\Models\CounterpartyType;
use App\Models\OPFType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class MainController extends AccountAbstract
{
    use JsonResponses;

    const ValidationRules = [
        'name' => 'required|min:2|max:80',
        'inn' => 'max:50',
        'ogrn' => 'max:50',
        'kpp' => 'max:50',
        'address' => 'max:150',
        'legal_address' => 'max:150',
        'category_id' => 'nullable|exists:App\Models\CounterpartyCategory,id',
        'opf_type_id' => 'nullable|exists:App\Models\OPFType,id'
    ];


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/counterparties",
     *      operationId="ui-counterparties",
     *      tags={"UI"},
     *      summary="Получить список контрагентов",
     *      description="Получить список контрагентов
     * - контрагенты
     * - типы контрагентов
     * - категории контрагентов ",
     *      @OA\Parameter(
     *          name="subdomain",
     *          in="path",
     *          required=true,
     *          description="Субдомен аккаунта Linerfin",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="profile"
     *      ),
     *      security={{"bearerAuth":{}}},  
     *       * @OA\Response(
     *     response=200,
     *     description="Успешный ответ",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="counterparties",
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"LEGAL", "INDIVIDUAL"}),
     *                 @OA\Property(property="category_id", type="integer", nullable=true),
     *                 @OA\Property(property="opf_type_id", type="integer", nullable=true),
     *                 @OA\Property(property="inn", type="string"),
     *                 @OA\Property(property="ogrn", type="string", nullable=true),
     *                 @OA\Property(property="kpp", type="string", nullable=true),
     *                 @OA\Property(property="address", type="string", nullable=true),
     *                 @OA\Property(property="legal_address", type="string", nullable=true),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="deleted_at", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string"),
     *                 @OA\Property(property="amo_company_id", type="integer", nullable=true)
     *             )
     *         ),
     *         @OA\Property(property="last_page", type="integer"),
     *         @OA\Property(property="categories", type="array", @OA\Items()),
     *         @OA\Property(
     *             property="opf_types",
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="short_name", type="string"),
     *                 @OA\Property(property="type", type="string", nullable=true),
     *                 @OA\Property(property="code", type="integer", nullable=true),
     *                 @OA\Property(property="for_individual", type="integer", nullable=true),
     *                 @OA\Property(property="for_legal", type="integer", nullable=true)
     *             )
     *         ),
     *         @OA\Property(property="success", type="boolean", default=true)
     *     )
     * ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     
     * Получить список контрагентов
     * - контрагенты
     * - типы контрагентов
     * - категории контрагентов
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {

        $data = [];

        /* STEP: Get counterparty collection
        -------------------------------------------*/
        $model = $this->getModel();

        if ($model instanceof Builder) {

            // Apply filters
            if ($category_id = $this->request->get('category'))
                $model->whereCategoryId($category_id);

            if ($opf_type_id = $this->request->get('opf'))
                $model->where('opf_type_id', $opf_type_id);

            // Search
            if ($s = $this->request->get('search')) {
                $model->where(function ($query) use ($s) {
                    $query->where('name', 'LIKE', "%$s%")
                        ->orWhere('inn', 'LIKE', "%$s%")
                        ->orWhere('ogrn', 'LIKE', "%$s%")
                        ->orWhere('kpp', 'LIKE', "%$s%")
                        ->orWhere('address', 'LIKE', "%$s%")
                        ->orWhere('legal_address', 'LIKE', "%$s%");
                });

                $model->orWhereHas('contacts', function (Builder $query) use ($s) {
                    $query->where('name', 'LIKE', "%$s%")
                        ->orWhere('surname', 'LIKE', "%$s%")
                        ->orWhere('patronymic', 'LIKE', "%$s%")
                        ->orWhere('email', 'LIKE', "%$s%");
                });
            }


            // Get counterparties
            $data['counterparties'] = $model->orderByDesc('created_at')->get();

            if (!empty($this->paginator))
                $data['last_page'] = $this->paginator->lastPage();
        }


        // Get categories
        $data['categories'] = CounterpartyCategory::whereAccountId($this->account_id)->get();

        // Get types
        $data['opf_types'] = OPFType::all();

        return $this->success($data);
    }


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/counterparties/{counterparty_id}",
     *      operationId="ui-counterparties-id",
     *      tags={"UI"},
     *      summary="Получить контрагента",
     *      description="Получить информацию о контрагенте ",
     *      @OA\Parameter(
     *          name="subdomain",
     *          in="path",
     *          required=true,
     *          description="Субдомен аккаунта Linerfin",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="profile"
     *      ),
     *          @OA\Parameter(
     *          name="counterparty_id",
     *          in="path",
     *          required=true,
     *          description="Субдомен аккаунта Linerfin",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="436"
     *      ),
     *      
     *      security={{"bearerAuth":{}}},  
     *       * @OA\Response(
     *     response=200,
     *     description="Успешный ответ",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="counterparty",
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="account_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="type", type="string", enum={"LEGAL", "INDIVIDUAL"}),
     *             @OA\Property(property="category_id", type="integer", nullable=true),
     *             @OA\Property(property="opf_type_id", type="integer", nullable=true),
     *             @OA\Property(property="inn", type="string"),
     *             @OA\Property(property="ogrn", type="string", nullable=true),
     *             @OA\Property(property="kpp", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="legal_address", type="string", nullable=true),
     *             @OA\Property(property="comment", type="string", nullable=true),
     *             @OA\Property(property="deleted_at", type="string", nullable=true),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *             @OA\Property(property="amo_company_id", type="integer", nullable=true)
     *         ),
     *         @OA\Property(property="success", type="boolean", default=true)
     *     )
     * ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     
     * Получить контрагент
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {

        // ID required
        $counterparty_id = $this->request->route('counterparty_id');
        $model = $this->getModel(true, false);

        if (!empty($counterparty_id) && $model instanceof Builder) {

            $counterparty = $model->where('id', $counterparty_id)->first();

            if (!empty($counterparty))
                return $this->success(compact('counterparty'));
        }

        return $this->error([], 'Контрагент не найден', 404);
    }


    // Объект пагинатора
    protected $paginator = null;



    /**
     * Получить коллекцию Counterparty
     * - референсы
     * - пагинация
     * - контрагенты компании
     * @param bool $with
     * @param bool $pagination
     * @return false | Counterparty
     */
    protected function getModel($with = true, $pagination = true)
    {

        $model = Counterparty::whereAccountId($this->account_id);

        if ($with && $with = $this->request->get('with'))
            $model->with(explode(',', $with));

        if ($pagination && $ppc = $this->request->get('ppc', 15))
            $this->paginator = $model->paginate($ppc);

        if (isset($_GET['trashed']))
            $model->withTrashed();

        return $model;
    }



    /*
     * Создание контрагента
     */
    public function create()
    {

        // Get request data
        $data = $this->request->validate(self::ValidationRules);

        /* STEP: Create model
        -------------------------------------------*/
        $counterparty = new Counterparty($data);
        $counterparty->account()->associate($this->account); // connect to account


        /* STEP: Get category
        -------------------------------------------*/
        if (!empty($data['category_id'])) {
            $category = $this->getBuilder(CounterpartyCategory::class, [
                'id' => $data['category_id']
            ])->first();
            $counterparty->category()->associate($category);
        }

        /* STEP: Get type
        -------------------------------------------*/
        if (!empty($data['opf_type_id'])) {
            $counterparty->opf()->associate(OPFType::find($data['opf_type_id']));
        }


        // save it
        if (!$counterparty->save())
            return $this->error('Не удалось создать контрагент');

        if (!$this->updateContacts($counterparty))
            return $this->error(['errors' => $this->getLastErrors()], 'Не удалось сохранить контакты');

        if (!$this->updateCheckingAccounts($counterparty))
            return $this->error(['errors' => $this->getLastErrors()], 'Не удалось сохранить банковские реквизиты');


        return $this->success(compact('counterparty'));
    }

    protected $lastErrors;
    public function getLastErrors()
    {
        if (empty($this->lastErrors))
            return null;

        if ($this->lastErrors instanceof MessageBag)
            return $this->lastErrors->toArray();

        return $this->lastErrors;
    }

    public function updateContacts(Counterparty $counterparty)
    {

        /* STEP: Get contacts from request
        -------------------------------------------*/
        $contacts = $this->request->input('contacts');
        if (!empty($contacts) && is_array($contacts)) {

            foreach ($contacts as $contact) {
                $validator = Validator::make($contact, [
                    'id' => 'exists:App\Models\Contact,id',
                    'name' => 'required|max:40',
                    'surname' => 'nullable|max:40',
                    'partonymic' => 'nullable|max:40',
                    'phone' => 'nullable|max:40',
                    'email' => 'nullable|max:40',
                    'main_contact' => 'nullable|boolean'
                ]);

                $validator->setAttributeNames(['name' => 'Имя']);

                if ($validator->fails() && $this->lastErrors = $validator->errors())
                    return false;
            }

            // Save contacts
            foreach ($contacts as $data) {

                // Update
                if (!empty($data['id'])) {
                    $contact = $this->getBuilder(Contact::class, [
                        'id' => $data['id']
                    ])->first();

                    $contact->update($data);
                }

                // Or create new
                else $contact = new Contact($data);

                $contact->counterparty()->associate($counterparty);
                $contact->account()->associate($this->account);
                $contact->save();
            }
        }



        /* STEP: Remove contacts
        -------------------------------------------*/
        $remove = $this->request->input('remove_contacts');
        if ($remove) {
            $remove = explode(',', $remove);
            $this->getBuilder(Contact::class)
                ->whereIn('id', $remove)
                ->delete();
        }

        return true;
    }

    public function updateCheckingAccounts(Counterparty $counterparty)
    {

        $accounts = $this->request->input('accounts');
        if (!empty($accounts) && is_array($accounts)) {
            foreach ($accounts as $account) {
                $validator = Validator::make($account, [
                    'id' => 'exists:App\Models\CounterpartyAccount,id',
                    'checking_num' => 'max:40',
                    'main_account' => 'nullable|boolean',
                    'bank_name' => 'max:55',
                    'bank_bik' => 'max:25',
                    'bank_swift' => 'max:25',
                    'bank_inn' => 'max:25',
                    'bank_kpp' => 'max:25',
                    'bank_correspondent' => 'max:40',
                ]);

                if ($validator->fails() && $this->lastErrors = $validator->errors())
                    return false;
            }

            foreach ($accounts as $account) {

                // Update
                if (!empty($account['id'])) {
                    $chAccount = $this->getBuilder(CounterpartyAccount::class, [
                        'id' => $account['id']
                    ])->first();
                    $chAccount->update($account);
                }

                // Create new
                else {
                    $chAccount = new CounterpartyAccount($account);
                }
                $chAccount->counterparty()->associate($counterparty);
                $chAccount->account()->associate($this->account);


                $chAccount->save();
            }
        }


        /* STEP: Remove accounts
        -------------------------------------------*/
        if ($remove = $this->request->input('remove_accounts')) {
            $remove_ids = explode(',', $remove);
            $this->getBuilder(CounterpartyAccount::class)
                ->whereIn('id', $remove_ids)
                ->delete();
        }

        return true;
    }




    /*
     * СОХРАНИТЬ ИЗМЕНЕНИЯ
     */
    public function update()
    {

        /* STEP: Prepare
        -------------------------------------------*/
        $data = $this->request->validate(array_merge(self::ValidationRules, [
            'name' => 'min:2|max:80'
        ]));

        $counterparty_id = $this->request->route('counterparty_id');

        $counterparty = Counterparty::where([
            'account_id' => $this->account_id,
            'id' => $counterparty_id
        ])->first();

        if (!$counterparty)
            return $this->error("Контрагент не найден");


        /* STEP: Save changes
        -------------------------------------------*/
        if (!$counterparty->update($data))
            return $this->error('Не удалось сохранить изменения');

        // Update category
        if (!empty($data['category_id'])) {
            $category = $this->getBuilder(CounterpartyCategory::class, [
                'id' => $data['category_id']
            ])->first();

            $counterparty->category()->associate($category);
        } else $counterparty->category()->dissociate();

        // Update type
        if (!empty($data['opf_type_id'])) {
            $type = OPFType::find($data['opf_type_id']);
            $counterparty->opf()->associate($type);
        }

        $counterparty->save();


        if (!$this->updateContacts($counterparty))
            return $this->error('Не удалось обновить контакты');

        if (!$this->updateCheckingAccounts($counterparty))
            return $this->error('Не удалось обновить счета контрагента');


        return $this->success(compact('counterparty'));
    }


    public function delete()
    {
    }

    public function deleteMany()
    {
        $ids = $this->request->input('ids');
        if (empty($ids))
            return $this->success();

        $ids = explode(',', $ids);

        $this->getBuilder(Counterparty::class)->whereIn('id', $ids)->delete();

        return $this->success();
    }
}
