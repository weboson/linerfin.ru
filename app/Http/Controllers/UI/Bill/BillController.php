<?php

namespace App\Http\Controllers\UI\Bill;

use App\Http\Abstraction\AccountAbstract;
use App\Http\Controllers\AmoCRM\AmoCRMController;
use App\Http\Controllers\SmsRuProvider;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Bill;
use App\Models\BillPosition;
use App\Models\BillSignature;
use App\Models\CheckingAccount;
use App\Models\Counterparty;
use App\Models\NDSType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class BillController extends AccountAbstract
{

    // Разрешенные зависимости для получения списком
    protected $allowedWith = [
        'account',
        'positions',
        'positions.nds_type',
        'checking_account',
        'template',
        'counterparty',
        'signature_list',
        'signature_list.signature_attachment',
        'signature_list_with_attachments',
        'stamp_attachment',
        'logo_attachment',
        'transactions',
        'transactions.toCheckingAccount',
        'transactions.counterparty',
        'transactions.budgetItem',
        'transactions.project',
    ];


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/bills",
     *      operationId="ui-bills",
     *      tags={"UI"},
     *      summary="Получение информации о пользователе и его аккаунтах",
     *      description="Возвращает информацию о пользователе и его аккаунтах. Для каждого аккаунта возвращаются списки контрагентов и счетов.",
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
     *     @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="bills", type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="nds_type_id", type="integer", nullable=true),
     *                 @OA\Property(property="template_id", type="integer", nullable=true),
     *                 @OA\Property(property="amocrm_account_id", type="integer"),
     *                 @OA\Property(property="amocrm_lead_id", type="integer"),
     *                 @OA\Property(property="amocrm_customer_id", type="integer", nullable=true),
     *                 @OA\Property(property="counterparty_id", type="integer"),
     *                 @OA\Property(property="checking_account_id", type="integer"),
     *                 @OA\Property(property="stamp_attachment_id", type="integer", nullable=true),
     *                 @OA\Property(property="logo_attachment_id", type="integer", nullable=true),
     *                 @OA\Property(property="num", type="string", nullable=true),
     *                 @OA\Property(property="sum", type="integer"),
     *                 @OA\Property(property="sum_without_vat", type="integer"),
     *                 @OA\Property(property="pay_before", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="issued_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="realized_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="payer_phone", type="string", nullable=true),
     *                 @OA\Property(property="payer_email", type="string", nullable=true),
     *                 @OA\Property(property="enable_attachments", type="integer"),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="reject_comment", type="string", nullable=true),
     *                 @OA\Property(property="link", type="string"),
     *                 @OA\Property(property="private_key", type="string"),
     *                 @OA\Property(property="access", type="string"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="transactions_sum_amount", type="integer", nullable=true)
     *             )
     *         ),
     *         @OA\Property(property="last_page", type="integer"),
     *         @OA\Property(property="templates", type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="nds_type_id", type="integer", nullable=true),
     *                 @OA\Property(property="template_id", type="integer", nullable=true),
     *                 @OA\Property(property="amocrm_account_id", type="integer"),
     *                 @OA\Property(property="amocrm_lead_id", type="integer"),
     *                 @OA\Property(property="amocrm_customer_id", type="integer", nullable=true),
     *                 @OA\Property(property="counterparty_id", type="integer"),
     *                 @OA\Property(property="checking_account_id", type="integer"),
     *                 @OA\Property(property="stamp_attachment_id", type="integer", nullable=true),
     *                 @OA\Property(property="logo_attachment_id", type="integer", nullable=true),
     *                 @OA\Property(property="num", type="string"),
     *                 @OA\Property(property="sum", type="integer"),
     *                 @OA\Property(property="sum_without_vat", type="integer"),
     *                 @OA\Property(property="pay_before", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="issued_at", type="string", format="date-time"),
     *                 @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="realized_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="payer_phone", type="string", nullable=true),
     *                 @OA\Property(property="payer_email", type="string", nullable=true),
     *                 @OA\Property(property="enable_attachments", type="integer"),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="reject_comment", type="string", nullable=true),
     *                 @OA\Property(property="link", type="string"),
     *                 @OA\Property(property="private_key", type="string"),
     *                 @OA\Property(property="access", type="string"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         ),
     *         @OA\Property(property="success", type="boolean")
     *     )
     * ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     
     * Get list of bills
     * Получить список счетов
     * > GET[with]
     * > GET[page]
     * > GET[ppc] // page per count
     * < { bills, last_page, templates }
     * @return \App\Http\Responses\JsonResponse
     */
    public function bills()
    {

        // Create query builder
        /** @var Builder $query */
        $query = Bill::whereAccountId($this->account_id);

        // Get add-on params
        $this->bindWith($query);
        $this->applyFilters($query);

        // Search by amocrm account
        $this->bindAmoCRM($query);
        $query->withSum('transactions', 'amount');

        // Get pagination params
        $paginator = $this->paginate($query);

        return $this->success([
            'bills' => $query->orderBy('created_at', 'desc')->get(),
            'last_page' => $paginator->lastPage(),
            'templates' => Bill::where([
                'status' => 'template',
                'account_id' => $this->account_id
            ])->get()
        ]);
    }



    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/bills/{id}",
     *      operationId="ui-bills-id",
     *      tags={"UI"},
     *      summary=" Получить счет",
     *      description="Возвращает полную информацию о счете",
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
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Идентификатор счета",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="588"
     *      ),
     *      security={{"bearerAuth":{}}},  
     *      @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="bill", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="account_id", type="integer"),
     *             @OA\Property(property="nds_type_id", type="integer", nullable=true),
     *             @OA\Property(property="template_id", type="integer", nullable=true),
     *             @OA\Property(property="amocrm_account_id", type="integer"),
     *             @OA\Property(property="amocrm_lead_id", type="integer"),
     *             @OA\Property(property="amocrm_customer_id", type="integer", nullable=true),
     *             @OA\Property(property="counterparty_id", type="integer"),
     *             @OA\Property(property="checking_account_id", type="integer"),
     *             @OA\Property(property="stamp_attachment_id", type="integer", nullable=true),
     *             @OA\Property(property="logo_attachment_id", type="integer", nullable=true),
     *             @OA\Property(property="num", type="string"),
     *             @OA\Property(property="sum", type="integer"),
     *             @OA\Property(property="sum_without_vat", type="integer"),
     *             @OA\Property(property="pay_before", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="issued_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="realized_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="payer_phone", type="string", nullable=true),
     *             @OA\Property(property="payer_email", type="string", nullable=true),
     *             @OA\Property(property="enable_attachments", type="integer"),
     *             @OA\Property(property="comment", type="string", nullable=true),
     *             @OA\Property(property="reject_comment", type="string", nullable=true),
     *             @OA\Property(property="link", type="string"),
     *             @OA\Property(property="private_key", type="string"),
     *             @OA\Property(property="access", type="string"),
     *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="transactions_sum_amount", type="integer")
     *         ),
     *         @OA\Property(property="success", type="boolean")
     *        )
     *      ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     
   
     * Get bill
     * Получить счет
     * > ROUTE[id] // bill_id
     * < { bill }
     * @return \App\Http\Responses\JsonResponse
     */
    public function get()
    {

        // Get bill id
        $bill_id = $this->request->route('id');
        if (empty($bill_id))
            return $this->error('Счет не найден');

        // Create query builder
        $query = Bill::where([
            'id' => $bill_id,
            'account_id' => $this->account_id
        ]);

        // Load ref models and get ref-list
        $with = $this->bindWith($query);
        $query->withSum('transactions', 'amount');

        // Get bill
        $bill = $query->first();
        if (!$bill)
            return $this->error('Счет не найден');

        // load signature attachments [if need]
        if (false !== array_search('signature_list', $with))
            $bill->signature_list->load('signature_attachment');


        return $this->success(compact('bill'));
    }



    /**
     * Apply filters to builder
     * Применить фильтры к конструктору
     * [похоже функция не закончена]
     * @param Builder $builder
     */
    public function applyFilters(Builder $builder)
    {
        $builder->where('status', '!=', 'template');
    }


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/bills/{id}/positions",
     *      operationId="ui-bills-id-position",
     *      tags={"UI"},
     *      summary="Получить позиции товаров и услуг",
     *      description="Возвращает информацию о позиции счета.",
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
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Идентификатор счета",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="588"
     *      ),
     *      security={{"bearerAuth":{}}},  
     *      * @OA\Response(
     *     response=200,
     *     description="Успешный ответ",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(
     *             property="positions",
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1397),
     *                 @OA\Property(property="account_id", type="integer", example=7),
     *                 @OA\Property(property="bill_id", type="integer", example=588),
     *                 @OA\Property(property="name", type="string", example="ывладыаыва"),
     *                 @OA\Property(property="vendor_code", type="string", nullable=true),
     *                 @OA\Property(property="unit_price", type="integer", example=3232),
     *                 @OA\Property(property="count", type="integer", example=23),
     *                 @OA\Property(property="units", type="string", example="шт"),
     *                 @OA\Property(property="nds_type_id", type="integer", nullable=true)
     *             )
     *         ),
     *         @OA\Property(property="success", type="boolean", example=true)
     *     )
     * ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     
     * Get bill's positions
     * Получить позиции товаров и услуг
     * > ROUTE[id] // bill_id
     * < { positions }
     * @return \App\Http\Responses\JsonResponse
     */
    public function positions()
    {
        $bill_id = $this->request->route('id');
        if (empty($bill_id))
            return $this->error('Счет не найден');

        $bill = Bill::where([
            'id' => $bill_id,
            'account_id' => $this->account_id
        ])->first();

        if (empty($bill))
            return $this->error('Счет не найден');

        return $this->success(['positions' => $bill->positions]);
    }


    /**
     * @OA\Post(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/bills/save",
     *      operationId="ui-bills-save",
     *      tags={"UI"},
     *     summary="Сохранение данных",
     *     description="Метод для сохранения данных счета.",
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
     *     
     *     @OA\RequestBody(
     *         description="Данные для сохранения счета",
     *         required=true,
     *         @OA\JsonContent( 
     *              allOf={
     *                   @OA\Schema(
     *                          @OA\Property(property="strict", type="boolean", description="Строгая проверка", example="true"),
     *                          @OA\Property(property="id", type="integer", nullable=true, description="ID счета"),
     *                          @OA\Property(property="template_id", type="integer", nullable=true, description="ID шаблона счета"),
     *                          @OA\Property(property="nds_type_id", type="integer", nullable=true, description="ID типа НДС"),
     *                          @OA\Property(property="status", type="string", nullable=true, enum={"draft", "issued", "rejected", "template", "paid", "realized", "realized-paid"}, description="Статус счета"),
     *                          @OA\Property(property="stamp_attachment_id", type="integer", nullable=true, description="ID вложения штампа"),
     *                          @OA\Property(property="logo_attachment_id", type="integer", nullable=true, description="ID вложения логотипа"),
     *                          @OA\Property(property="counterparty_id", type="integer", nullable=true, description="ID контрагента"),
     *                          @OA\Property(property="checking_account_id", type="integer", nullable=true, description="ID расчетного счета"),
     *                          @OA\Property(property="account_id", type="integer", nullable=true, description="ID компании"),
     *                          @OA\Property(property="num", type="string", nullable=true, maxLength=30, description="Номер счета"),
     *                          @OA\Property(property="pay_before", type="string", format="date", nullable=true, description="Дата оплаты"),
     *                          @OA\Property(property="payer_phone", type="string", nullable=true, maxLength=25, description="Телефон плательщика"),
     *                          @OA\Property(property="payer_email", type="string", format="email", nullable=true, maxLength=100, description="Email плательщика"),
     *                          @OA\Property(property="enable_attachments", type="boolean", nullable=true, description="Включить вложения"),
     *                          @OA\Property(property="amocrm_account_id", type="integer", nullable=true, description="ID учетной записи amoCRM"),
     *                          @OA\Property(property="amocrm_lead_id", type="integer", nullable=true, description="ID лида amoCRM"),
     *                          @OA\Property(property="amocrm_customer_id", type="integer", nullable=true, description="ID клиента amoCRM"),
     *                          @OA\Property(property="comment", type="string", nullable=true, maxLength=250, description="Комментарий"),
     *                          @OA\Property(property="name", type="string", maxLength=255, description="Название позиции", example="Товар 1"),
     *                          @OA\Property(property="unit_price", type="number", minimum=0, description="Цена за единицу", example=100.50),
     *                          @OA\Property(property="count", type="integer", minimum=1, description="Количество", example=5),
     *                          @OA\Property(property="units", type="string", maxLength=50, nullable=true, description="Единицы измерения"),
     *                          @OA\Property(property="nds_type", type="integer", nullable=true, description="ID типа НДС для позиции"),
     *                          @OA\Property(property="position", type="string", maxLength=150, nullable=true, description="Должность подписанта"),
     *                          @OA\Property(property="full_name", type="string", maxLength=150, nullable=true, description="Полное имя подписанта"),
     *                          @OA\Property(property="signature_attachment_id", type="integer", nullable=true, description="ID вложения подписи"),
     *     
     *                    )
     *              }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID счета"),
     *             @OA\Property(property="bill", type="object", description="Информация о счете"),
     *             @OA\Property(property="updated_at", type="integer", description="Время последнего обновления")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации или обработки запроса",
     *         
     *     )
     * )
    
     * Save or update bill
     * Сохранение или обновление счета
     * > input fields
     *   входящие данные из BillValidation::class
     * @return \App\Http\Responses\JsonResponse
     */
    public function save()
    {

        /* STEP: Get start data
        -------------------------------------------*/
        // get status [ draft | issued | rejected | template | paid ]
        $status = $this->request->input('status', 'draft');


        // strict validation
        // строгая проверка к публикации
        $strict = $this->request->input('strict');
        $strict = $strict ?: $status !== 'draft' && $status !== 'template';

        /*
             * 1. если strict режим включен
             *    - обязательно к заполнению контрагент, расчетный счет, компании и т.д.
             * 2. strict не используется в режиме черновика или шаблона
             */


        /* STEP: Validation
        -------------------------------------------*/
        // validate request [and get data]
        if ($strict)
            $data = BillValidation::toPublish(null, [], $errors);
        else
            $data = BillValidation::draft(null, [], $errors);

        if (false !== array_search($status, ['paid', 'issued'])) {
            unset($data['status']); // remove status [set later / in the end]
        }

        // return validation errors
        if (!empty($errors))
            return $this->error(compact('errors'));


        // Check pay before ...
        if ($data['pay_before']) {
            $pay_before = strtotime($data['pay_before']);
            if ($pay_before < strtotime(date('d.m.Y 59:59:59')))
                return $this->error('Дата оплаты не может быть раньше текущего дня');
        }



        /* STEP: Update or create
        -------------------------------------------*/
        $bill_id = $this->request->route('id');

        if (!empty($bill_id)) {
            $bill = $this->getBuilder(Bill::class, [
                'id' => $bill_id
            ])->first();

            if (empty($bill)) return $this->error('Счёт не найден');
            $bill_old_status = $bill->status;
            $bill->update($data);
        } else
            $bill = new Bill($data);


        $bill->access = $status === 'issued' ? 'public' : 'account';
        if ($status === 'issued' && empty($bill->pay_before))
            $bill->pay_before = date('Y-m-d 23:59:59');



        /* STEP: Associate other dependencies
        -------------------------------------------*/

        // 1. Account [компания]
        if (!empty($data['account_id'])) {
            $company = Account::where([
                'id' => $data['account_id'],
                'user_id' => $this->account->user_id
            ])->first();

            if ($company) {
                $bill->account()->associate($company);
            }
        } else $bill->account()->associate($this->account);


        // 2. Counterparty [контрагент]
        $counterparty_save_result = $this->saveCounterparty($bill, $strict);
        if (!Arr::get($counterparty_save_result, 'success')) {
            $msg = Arr::get($counterparty_save_result, 'msg');
            return $this->error($msg);
        }


        // 3. Checking account [расчетный счет | банк]
        if (!empty($data['checking_account_id'])) {
            $ChAccount = $this->getBuilder(CheckingAccount::class, [
                'id' => $data['checking_account_id']
            ])->first();

            if ($ChAccount) {
                $bill->checking_account()->associate($ChAccount);
                if ($strict && (!$ChAccount->bank_name || !$ChAccount->bank_bik))
                    return $this->error([], 'Заполните реквизиты банка');
            }
        } else {
            if ($strict) {
                return $this->error([], 'Выберите расчетный счет');
            }

            $bill->checking_account()->dissociate();
        }


        // 4. amoCRM account [интеграция с amoCRM]
        $amocrm_account = AmoCRMController::getAccount();
        if ($amocrm_account)
            $bill->amocrm_account()->associate($amocrm_account);
        //            else
        //                $bill->amocrm_account()->dissociate();


        // 5. Logo attachment
        if (!empty($data['logo_attachment_id'])) {
            $logo = $this->getBuilder(Attachment::class, ['id' => $data['logo_attachment_id']])->first();
            if ($logo)
                $bill->logo_attachment()->associate($logo);
            else
                throw new \Exception('Логотип не найден');
        } else
            $bill->logo_attachment()->disassociate();


        // 6. Stamp attachment
        if (!empty($data['stamp_attachment_id'])) {
            $logo = $this->getBuilder(Attachment::class, ['id' => $data['stamp_attachment_id']])->first();
            if ($logo)
                $bill->stamp_attachment()->associate($logo);
            else
                throw new \Exception('Штамп не найден');
        } else
            $bill->logo_attachment()->disassociate();


        // 7. VAT type [nds_type]
        if (!empty($data['nds_type_id'])) {
            $nds_type = NDSType::find($data['nds_type_id']);
            if ($nds_type)
                $bill->nds_type()->associate($nds_type);
        }



        /* STEP: Save bill
        -------------------------------------------*/
        if (!$bill->save())
            return $this->error('Не удалось сохранить счёт');




        /* STEP: Save positions and signatures
        -------------------------------------------*/

        // 1. Positions
        // no-strict for drafts
        $positionRules = $strict ? [] : [
            'name' => 'nullable|max:255',
            'unit_price' => 'nullable|numeric',
            'count' => 'nullable|numeric',
        ];
        $valid = $this->saveBillPositions($bill, $positionRules);

        // check errors
        if ($this->errors_exists() || !$valid)
            return $this->error(['errors' => $this->get_errors()], 'Проверьте корректность заполнения списка товаров или услуг');



        // 2. Signatures
        if ($strict && !$bill->enable_attachments) {
            $signatureRules = [
                'signature_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
                'stamp_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
            ];
        } elseif (!$strict) {
            $signatureRules = [
                'position' => 'nullable|max:150',
                'full_name' => 'nullable|max:150',
                'signature_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
                'stamp_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
            ];
        } else
            $signatureRules = [];


        $valid = $this->saveBillSignature($bill, $signatureRules);

        // check errors
        if ($this->errors_exists() || !$valid)
            return $this->error(['errors' => $this->get_errors()], "Проверьте корректность заполнения списка подписей");



        /* STEP: Last checking
        -------------------------------------------*/

        if ($strict) {

            $bill->update(['status' => $status]);


            // For public check exists positions and signatures
            // todo: кажется не работает проверка
            if (!$bill->positions()->count() || !$bill->signature_list()->count()) {
                $bill->status = !empty($bill_old_status) ? $bill_old_status : 'draft';
                return $this->error('Для выставления счета заполните позиции и ответственных');
            }

            $bill->save();
        }



        /* STEP: Create template
        -------------------------------------------*/
        if ($status === 'issued' && $this->request->input('create_template')) {
            $templateName = $this->request->input('create_template_name');
            if (empty($templateName)) {
                $count = Bill::where([
                    'account_id' => $this->account_id,
                    'status' => 'template'
                ])->count();
                $templateName = $count ? "Новый шаблон " . ($count + 1) : "Новый шаблон";
            }

            // replicate
            $template = $bill->replicate();

            // update data
            $template->status = 'template';
            $template->template_id = null;
            $template->num = $templateName;
            $template->created_at = Carbon::now();

            $template->save();

            // push positions
            foreach ($bill->positions as $position) {
                $position = $position->replicate();
                $position->account()->associate($this->account);
                $position->bill()->associate($template);
                $position->save();
            }


            // push signatures
            foreach ($bill->signature_list as $signature) {
                $signature = $signature->replicate();
                $signature->account()->associate($this->account);
                $signature->bill()->associate($template);
                $signature->save();
            }
        }


        /* STEP: Client notification
        -------------------------------------------*/
        if ($bill->status === 'issued' && $this->request->input('send_notify')) {
            if ($payer_phone = $this->request->input('payer_phone'))
                $this->clientNotifyPhone($bill, $payer_phone);

            if ($payer_email = $this->request->input('payer_email'))
                $this->clientNotifyEmail($bill, $payer_email);
        }



        return $this->success([
            'id' => $bill->id,
            'bill' => $bill,
            'updated_at' => time()
        ]);
    }


    /**
     * Attach (save) counterparty
     * Прикрепить (сохранить) контрагента
     * > GET[counterparty_id]
     * <
     * @param Bill $bill
     * @param bool $strict
     * @return array|bool[]
     */
    protected function saveCounterparty(Bill $bill, bool $strict)
    {

        $counterparty_id = $this->request->input('counterparty_id');

        // If counterparty found by id
        if (!empty($counterparty_id)) {
            $counterparty = $this->getBuilder(Counterparty::class, [
                'id' => $counterparty_id
            ])->first();

            // associate
            if ($counterparty)
                $saveResult = $bill->counterparty()->associate($counterparty);

            if (!$counterparty || !$saveResult)
                return ['success' => false, 'msg' => 'Контрагент не найден'];
        }


        // If counterparty not exists -> create new
        if (empty($counterparty_id) || empty($counterparty)) {

            $data = $this->request->only([
                'counterparty_name', 'counterparty_inn', 'counterparty_kpp', 'counterparty_legal_address',
                'counterparty_ogrn'
            ]);

            // format inn, kpp and ogrn
            foreach ($data as $key => &$item) {
                if (false !== array_search($key, ['counterparty_inn', 'counterparty_kpp', 'counterparty_ogrn']))
                    $item = preg_replace('/[^0-9]/ui', '', $item);
            }

            $validator = Validator::make($data, [
                'counterparty_name'     => 'required|max:150',
                'counterparty_opf_type_id'  => 'nullable|exists:App\Models\OPFType,id',
                'counterparty_inn'      => 'nullable|max:15',
                'counterparty_ogrn'      => 'nullable|max:20',
                'counterparty_kpp'      => 'nullable|max:20',
                'counterparty_legal_address'  => 'nullable|max:150'
            ]);

            if ($validator->fails()) {

                // for not strict checking
                if (!$strict)
                    return ['success' => true];

                // for strict
                else
                    return ['success' => false, 'msg' => ['errors' => $validator->errors()]];
            }

            if (!empty($data)) {
                $inn = Arr::get($data, 'counterparty_inn');
                $counterparty = null;

                // Search counterparty by inn
                if (!empty($inn)) {
                    $counterparty = Counterparty::where([
                        'account_id' => $this->account_id,
                        'inn' => $inn
                    ])->first();
                }

                // Create counterparty if not exists
                if (empty($counterparty)) {
                    $counterparty = new Counterparty([
                        'name' => Arr::get($data, 'counterparty_name'),
                        'inn' => Arr::get($data, 'counterparty_inn'),
                        'ogrn' => Arr::get($data, 'counterparty_ogrn'),
                        'kpp' => Arr::get($data, 'counterparty_kpp'),
                        'legal_address' => Arr::get($data, 'counterparty_legal_address'),
                    ]);
                    $counterparty->account()->associate($this->account);
                    $counterparty->save();
                }

                if (!empty($counterparty))
                    $bill->counterparty()->associate($counterparty);
            }


            // Check on counterparty
            if (!$bill->counterparty()) {

                // for strict - counterparty required!
                // для строгой проверки обязателен контрагент
                if ($strict)
                    return ['success' => false, 'msg' => 'Заполните данные о контрагенте'];


                $bill->counterparty()->dissociate();
            }
        }

        return ['success' => true];
    }


    /**
     * Save bill positions
     * Сохранить позиции счёта
     * @param Bill $bill
     * @param array $rules
     * @return bool
     */
    protected function saveBillPositions(Bill $bill, array $rules = [])
    {
        $positions = $this->request->input('positions');
        if (!isset($positions))
            return true;

        if (!is_array($positions)) {
            $bill->positions()->delete();
            $bill->sum = 0;
            $bill->save();

            return true;
        }

        // Validate positions
        foreach ($positions as $position) {
            $valid = BillValidation::position($position, $rules, $this->lastErrors);
            if (!$valid || !empty($this->lastErrors))
                return false;
        }


        // Remove old list
        $bill->positions()->delete();

        // Save positions
        $billSum = 0;
        foreach ($positions as $data) {
            $position = new BillPosition($data);
            $position->bill()->associate($bill);
            $position->account()->associate($this->account);
            $position->save();
            $billSum += $position['unit_price'] * $position['count'];
        }

        $bill->sum = $billSum;
        $bill->save();

        return true;
    }


    /**
     * Saving bill's signature list
     * Сохранить список ответственных
     * @param Bill $bill
     * @param array $rules
     * @return bool
     */
    protected function saveBillSignature(Bill $bill, array $rules = [])
    {

        // Get request data
        $signatureList = $this->request->input('signature');
        if (!isset($signatureList))
            return true;

        // Remove end return if not exists
        if (!is_array($signatureList) || !count($signatureList)) {
            $bill->signature_list()->delete();
            return true;
        }

        // Validate
        foreach ($signatureList as $signature) {
            $valid = BillValidation::signature($signature, $rules, $this->lastErrors);
            if (!$valid || !empty($this->lastErrors))
                return false;
        }


        // Remove old list
        $bill->signature_list()->delete();


        // Cycle saving
        foreach ($signatureList as $data) {

            // for empty signatures
            if (empty($data) || !count($data))
                continue;

            $signature = new BillSignature($data);
            $signature->account()->associate($this->account);
            $signature->bill()->associate($bill);

            // Bind signature attachment
            if (!empty($data['signature_attachment_id'])) {
                $attachment = $this->getBuilder(Attachment::class, [
                    'id' => $data['signature_attachment_id']
                ])->first();

                if ($attachment) $signature->signature_attachment()->associate($attachment);
            }


            $signature->save();
        }

        return true;
    }



    /**
     * Delete bill
     * Удалить счет
     * @param Request $request
     * @param string $subdomain
     * @param string $bill_id
     * @return \App\Http\Responses\JsonResponse
     */
    public function delete(Request $request, $subdomain = '', $bill_id = '')
    {

        if (empty($bill_id) || !$bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first())
            return $this->error('Счет не найден');

        if (!$bill->delete())
            return $this->error('Не удалось удалить счет');

        return $this->success();
    }


    /**
     * Отправить уведомление клиенту
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse|void
     */
    public function sendNotifyClient(Request $request)
    {
        $bill_id = $request->route('id');
        $payer_phone = $request->input('payer_phone');
        $payer_email = $request->input('payer_email');


        // Get bill
        if (!$bill_id)
            return $this->error('Ошибка отправки уведомления');

        $bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first();
        if (empty($bill))
            return $this->error('Счёт не найден');

        // Send email
        if ($payer_email) {
            if (!$this->clientNotifyEmail($bill, $payer_email))
                return $this->error('Произошла ошибка при отправке уведомления на ' . $payer_email);
        }

        if ($payer_phone) {
            if (!$this->clientNotifyPhone($bill, $payer_phone))
                return $this->error('Произошла ошибка при отправке SMS-уведомления на ' . $payer_phone);
        }

        return $this->success();
    }



    /**
     * Remove some bills
     * Удалить счета (несколько)
     * @return \App\Http\Responses\JsonResponse
     */
    public function deleteMany()
    {
        $ids = $this->request->input('ids');
        if (empty($ids))
            return $this->success();

        $ids = explode(',', $ids);

        $this->getBuilder(Bill::class)->whereIn('id', $ids)->delete();

        return $this->success();
    }


    /**
     * Remove draft bill
     * Удалить черновик
     * @param Request $request
     * @param string $subdomain
     * @param string $bill_id
     * @return \App\Http\Responses\JsonResponse
     */
    public function removeDraft(Request $request, $subdomain = '', $bill_id = '')
    {

        if (!empty($bill_id)) {
            $bill = $this->getBuilder(Bill::class, [
                'id' => $bill_id
            ])->first();

            if ($bill->status !== 'draft')
                return $this->error("Счёт не является черновиком");

            if (!empty($bill) && $bill->delete())
                return $this->success();
        }

        return $this->error('Счет не найден');
    }




    /**
     * Reject bill
     * Отозвать счет
     * @param Request $request
     * @param string $subdomain
     * @param string $bill_id
     * @return \App\Http\Responses\JsonResponse
     */
    public function reject(Request $request, $subdomain = '', $bill_id = '')
    {


        // get bill
        if (empty($bill_id) || !$bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first())
            return $this->error('Счет не найден');


        // get comment
        $comment = $this->request->input('comment');


        // update status
        $bill->status = 'rejected';
        $bill->reject_comment = $comment;
        $bill->rejected_at = new Carbon($request->input('rejected_at'));
        $bill->paid_at = $bill->issued_at = $bill->realized_at = null;

        if (!$bill->save())
            return $this->error('Не удалось отозвать счёт');


        // send notification
        if ($this->request->input('send_notify')) {

            // get link
            $link = self::getBillLink($bill);
            if (!$link)
                return $this->error(['error' => ['bill link not exists']], 'Счёт отозван, но не удалось отправить уведомление');

            /*if($phone = $this->request->input('phone')){
                $sms = new SmsRuProvider();
                $sms->smsSend($phone, ($comment ?: 'Не оплачивайте этот счёт') . " $link");
            }*/

            if ($email = $this->request->input('email')) {
                Mail::send(
                    'emails.bill-reject',
                    compact('bill', 'comment', 'link'),
                    function ($m) use ($email) {
                        $m->from(env('MAIL_FROM_ADDRESS', 'index@linercrm.ru'), env('MAIL_FROM_NAME', 'LinerFin'));
                        $m->to($email);
                    }
                );
            }
        }

        return $this->success([], 'Счёт был отозван');
    }




    public function clientNotifyEmail(Bill $bill, $to, $toName = '')
    {

        // Get Link
        $link = self::getBillLink($bill);
        if (empty($link))
            return false;

        // PDF file name
        $filename = [];
        if ($bill->num)
            $filename[] = "Счёт №" . $bill->num;
        if ($bill->counterparty)
            $filename[] = $bill->counterparty->name;
        if ($bill->issued_at)
            $filename[] = '- от ' . $bill->issued_at->format('d-m-Y H-i');

        // check access
        if ($bill->access === 'account')
            return false;

        $filename = implode('', $filename) . '.pdf';


        \Illuminate\Support\Facades\Mail::send('emails.bill', [], function ($m) use ($to, $toName, $filename, $link) {
            $m->from(env('MAIL_FROM_ADDRESS', 'index@linercrm.ru'), env('MAIL_FROM_NAME', 'LinerFin'));
            $m->to($to, $toName);
            $m->subject('Новый счет к оплате');
            $pdf = new \mikehaertl\wkhtmlto\Pdf($link . "?print");
            $m->attachData($pdf->toString(), $filename);
        });

        return true;
    }



    public function clientNotifyPhone($bill, $to)
    {

        // get link
        $link = self::getBillLink($bill);
        if (empty($link))
            return false;

        $apiId = config('app.SMS_RU_API', '');
        $client = new \Zelenin\SmsRu\Api(new \Zelenin\SmsRu\Auth\ApiIdAuth($apiId), new \Zelenin\SmsRu\Client\Client());

        $sms = new \Zelenin\SmsRu\Entity\Sms($to, "Вам выставлен счет к оплате: $link");
        if ($client->smsSend($sms))
            return true;

        return false;
    }


    public static function getBillLink(Bill $bill)
    {

        if (empty($bill) || empty($bill->link)) return false;

        // Prepare link to PDF
        return (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')
            . config('app.domain')
            . "/bill-$bill->link";
    }




    // ---------------------------
    //    Attention! Died zone
    // ---------------------------


    // [disabled]
    public function create()
    {

        $data = $this->validatedRequest();

        /* STEP: Check counterparty
        -------------------------------------------*/
        if (!empty($data['counterparty_id'])) {

            $counterparty = $this->getBuilder(Counterparty::class, [
                'id' => $data['counterparty_id']
            ])->first();

            if (empty($counterparty))
                return $this->error('Контрагент не найден');

            unset($data['counterparty_id']);
        }


        /* STEP: Check bill's template
        -------------------------------------------*/
        if (!empty($data['template_id'])) {
            $template = $this->getBuilder(BillTemplate::class, [
                'id' => $data['template_id']
            ])->first();

            if (empty($template))
                return $this->error('Шаблон не найден');

            unset($data['template_id']);
        }


        /* STEP: Creating bill
        -------------------------------------------*/
        $bill = new Bill($data);

        $bill->account()->associate($this->account);
        if (!empty($counterparty))
            $bill->counterparty()->associate($counterparty);

        if (!empty($template))
            $bill->template()->associate($template);

        if (!$bill->save())
            return $this->error('Не удалось сохранить счет');

        // Saving positions
        if (!empty($this->request->input('positions'))) {
            if (!$this->saveBillPositions($bill)) // save positions
                return $this->error('Не удалось сохранить счет');
        }


        return $this->success(compact('bill'));
    }

    // [disabled]
    public function update()
    {

        $data = $this->validatedRequest();
        $bill_id = $this->request->route('id');

        if (empty($bill_id))
            return $this->error('Счет не найден');


        /* STEP: Check associates
        -------------------------------------------*/
        if (!empty($data['counterparty_id'])) {

            $counterparty = $this->getBuilder(Counterparty::class, [
                'id' => $data['counterparty_id']
            ])->first();

            if (empty($counterparty))
                return $this->error('Контрагент не найден');

            unset($data['counterparty_id']);
        }

        // Templates not work on updating
        if (!empty($data['template_id']))
            unset($data['template_id']);



        /* STEP: Get the bill and update this
        -------------------------------------------*/
        $bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first();

        if (empty($bill))
            return $this->error("Счет не найден");

        $bill->update($data);

        // Update counterparty
        if (!empty($counterparty))
            $bill->counterparty()->associate($counterparty);

        if (!$bill->save())
            return $this->error('Не удалось сохранить счет');


        return $this->success(compact('bill'));
    }


    // [disabled]
    public function updatePositions()
    {
        $bill_id = $this->request->route('id');
        if (empty($bill_id))
            return $this->error('Счет не найден');

        $bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first();
        if (empty($bill) || !($bill instanceof Bill))
            return $this->error('Счет не найден');

        $result = $this->saveBillPositions($bill);

        if (!$result)
            return $this->error('Не удалось обновить позиции счета');

        $bill->load('positions', 'counterparty', 'template');

        return $this->success(compact('bill'));
    }


    protected $lastErrors;
    public function errors_exists()
    {
        return !empty($this->lastErrors);
    }
    public function get_errors()
    {
        return $this->lastErrors;
    }


    /*
     * Сохранить позиции счета
     * @param Bill $bill
     * @return boolean
     */
    /*protected function saveBillPositions(Bill $bill){
        $positions = $this->request->input('positions');

        // If this is clear action
        if(!$positions && isset($_POST['positions'])){
            $bill->positions()->delete();
            return true;
        }

        // If positions var not exists
        if(!$positions || !is_array($positions))
            return true;

        // Validate positions
        foreach($positions as $position){
            $validator = Validator::make($position, [
                'unit_price' => 'required|numeric',
                'count' => 'required|numeric',
                'units' => 'nullable|string',
            ]);

            if($validator->fails())
                return false;
        }

        // Remove positions before inserting
        $bill->positions()->delete();

        // Calculate bill sum
        $sum = 0;

        // Insert new positions
        foreach($positions as $position){
            $bill->positions()->create($position);
            $sum += $position['unit_price'] * $position['count'];
        }

        $bill->sum = $sum;
        $bill->save();

        return true;
    }*/
    
    
    /**
     * Получить уникальные названия товаров/услуг для автодополнения
     */
    public function productSuggestions()
    {
        // Получить уникальные названия из позиций счетов текущего аккаунта
        $suggestions = \App\Models\BillPosition::where('account_id', $this->account_id)
            ->whereNotNull('name')
            ->where('name', '!=', '') // Исключить пустые
            ->distinct()
            ->limit(10) // Ограничение до 10 элементов
            ->pluck('name')
            ->toArray();

        return $this->success(['suggestions' => $suggestions]);
    }
}
