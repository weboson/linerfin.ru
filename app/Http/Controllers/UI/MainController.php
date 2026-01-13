<?php

namespace App\Http\Controllers\UI;


use App\Http\Abstraction\AccountAbstract;
use App\Models\Bank;
use App\Models\BudgetItemType;
use App\Models\Counterparty;
use App\Models\Account;
use App\Models\CounterpartyCategory;
use App\Models\CounterpartyType;
use App\Models\NDSType;
use App\Models\OPFType;
use Illuminate\Support\Facades\Auth;

class MainController extends AccountAbstract
{

    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://{subdomain}.linerfin.ru",
     *      ),
     *      path="/ui/bootstrap",
     *      operationId="ui-bootstrap",
     *      tags={"UI"},
     *      summary="сбор различных элементов данных, таких как баланс, бюджетные позиции, контрагенты, проекты и детали учетной записи",
     *      description="Подготовка Контроллеров:
     *   
     *   Создание экземпляров контроллеров (BalanceController, BudgetItemsController, ProjectsController) с проверкой авторизации.
     *   Получение Балансов:
     *   
     *   Получение информации о балансах с помощью BalanceController и объединение ее с данными ответа.
     *   Получение Бюджетных Позиций:
     *   
     *   Получение бюджетных позиций через BudgetItemsController и добавление их в данные ответа.
     *   Получение Контрагентов:
     *   
     *   Получение информации о контрагентах, связанных с текущей учетной записью, включая контакты, учетные записи, организационную форму и        *   категорию, и добавление их в данные ответа.
     *   Получение Проектов:
     *   
     *   Получение данных о проектах с помощью ProjectsController и объединение их с данными ответа.
     *   Получение Типов НДС и ОПФ:
     *   
     *   Получение типов НДС (Налог на добавленную стоимость) и организационно-правовых форм и включение их в данные ответа.
     *   Добавление Данных Учетной Записи и Пользователя:
     *   
     *   Загрузка деталей учетной записи, включая информацию о пользователе, логотипы и вложения, и добавление их в данные ответа.
     *   Загрузка данных о связанных учетных записях пользователя, включая вложения и подписи, и добавление их в данные ответа.
     *   Получение Типов Бюджетных Позиций:
     *   
     *   Получение всех типов бюджетных позиций и включение их в данные ответа.
     *   Результирующий массив данных содержит полный набор информации, необходимой для эффективной работы фронтенда или других частей      *   приложения. Этот метод помогает оптимизировать процесс инициализации и гарантирует доступность всех необходимых данных для     *   последующих операций.",
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
     *     description="Successful operation",
     *     @OA\JsonContent(
     *         @OA\Property(property="balance", type="object",
     *             @OA\Property(property="incomes", type="number", format="float"),
     *             @OA\Property(property="expenses", type="number", format="float"),
     *             @OA\Property(property="balance", type="number", format="float"),
     *             @OA\Property(property="total", type="number", format="float")
     *         ),
     *         @OA\Property(property="checking_accounts", type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="num", type="string", nullable=true),
     *                 @OA\Property(property="balance", type="number", format="float"),
     *                 @OA\Property(property="bank_name", type="string", nullable=true),
     *                 @OA\Property(property="bank_bik", type="string", nullable=true),
     *                 @OA\Property(property="bank_swift", type="string", nullable=true),
     *                 @OA\Property(property="bank_inn", type="string", nullable=true),
     *                 @OA\Property(property="bank_kpp", type="string", nullable=true),
     *                 @OA\Property(property="bank_correspondent", type="string", nullable=true),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="provider", type="string", nullable=true),
     *                 @OA\Property(property="o_auth_account_id", type="integer", nullable=true),
     *                 @OA\Property(property="provider_account_id", type="string", nullable=true),
     *                 @OA\Property(property="import_is_active", type="boolean"),
     *                 @OA\Property(property="provider_account_updated_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="provider_account_created_at", type="string", format="date-time", nullable=true),
     *             )
     *         ),
     *         @OA\Property(property="budget_items", type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="parent_id", type="integer", nullable=true),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="category", type="string"),
     *                 @OA\Property(property="type_id", type="integer"),
     *                 @OA\Property(property="group_id", type="integer", nullable=true),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="archived", type="integer"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="type", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="desc", type="string", nullable=true),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="category", type="string"),
     *                 ),
     *                 @OA\Property(property="group", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                 )
     *             )
     *         ),
     *         @OA\Property(property="groups", type="array",@OA\Items()),
     *         @OA\Property(property="types", type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="desc", type="string", nullable=true),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="category", type="string"),
     *             )
     *         ),
     *         @OA\Property(property="counterparties", type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="account_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="category_id", type="integer", nullable=true),
     *                 @OA\Property(property="opf_type_id", type="integer", nullable=true),
     *                 @OA\Property(property="inn", type="string"),
     *                 @OA\Property(property="ogrn", type="string", nullable=true),
     *                 @OA\Property(property="kpp", type="string", nullable=true),
     *                 @OA\Property(property="address", type="string", nullable=true),
     *                 @OA\Property(property="legal_address", type="string", nullable=true),
     *                 @OA\Property(property="comment", type="string", nullable=true),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="amo_company_id", type="integer", nullable=true),
     *                 @OA\Property(property="opf", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="short_name", type="string"),
     *                     @OA\Property(property="type", type="string", nullable=true),
     *                     @OA\Property(property="code", type="integer"),
     *                     @OA\Property(property="for_individual", type="integer", nullable=true),
     *                     @OA\Property(property="for_legal", type="integer", nullable=true),
     *                 ),
     *                 @OA\Property(property="category", type="object", nullable=true),
     *                 @OA\Property(property="accounts", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="account_id", type="integer"),
     *                         @OA\Property(property="balance", type="number", format="float"),
     *                         @OA\Property(property="main", type="integer"),
     *                         @OA\Property(property="type_id", type="integer"),
     *                         @OA\Property(property="currency_id", type="integer"),
     *                         @OA\Property(property="bik", type="string"),
     *                         @OA\Property(property="bank_name", type="string"),
     *                         @OA\Property(property="bank_correspondent", type="string"),
     *                         @OA\Property(property="bank_swift", type="string"),
     *                         @OA\Property(property="provider_id", type="integer"),
     *                         @OA\Property(property="provider_account_id", type="string"),
     *                         @OA\Property(property="provider_account_updated_at", type="string", format="date-time"),
     *                         @OA\Property(property="provider_account_created_at", type="string", format="date-time"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     )
     *                 ),
     *             )
     *         ),
     *     )
     * ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     */

    // Базовые данные для SPA
    // Base data for SPA
    public function bootstrap()
    {

        $data = []; // response

        // Prepare controllers
        $BlController = BalanceController::withAuthorize();
        $BIController = BudgetItemsController::withAuthorize();
        $PrController = ProjectsController::withAuthorize();


        // Get balances
        $data['balance'] = $BlController->getBalance();
        $data = array_merge($data, $BlController->getCheckingAccounts());

        // Get budget items
        $data = array_merge($data, $BIController->getBudgetItems());

        // Get counterparties
        $data['counterparties'] = Counterparty::whereAccountId($this->account_id)->with([
            'contacts', 'accounts', 'opf', 'category'
        ])->withTrashed()->get();
        $data['counterparty_categories'] = CounterpartyCategory::whereAccountId($this->account_id)->get();


        // Get projects
        $data = array_merge($data, $PrController->getProjects());

        // Get NDS types
        $data['nds_types'] = NDSType::get();

        $data['opf_types'] = OPFType::all();

        // Add account's data
        $account = $this->account;

        // Add user's data
        $account->load(['user', 'logo_attachment', 'stamp_attachment']);
        $account['user']->load('accounts');
        $account['user']['accounts']->load(['logo_attachment',  'stamp_attachment', 'director_signature', 'accountant_signature']);


        $data['account'] = $account;

        $data['budget_item_types'] = BudgetItemType::all();

        return $data;
    }
}
