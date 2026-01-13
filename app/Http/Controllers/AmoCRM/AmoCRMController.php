<?php

namespace App\Http\Controllers\AmoCRM;

use AmoCRM\Client\AmoCRMApiClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\AmoCRMSettingsRequest;
use App\Http\Traits\JsonResponses;
use App\Jobs\AmoCRM\BindWebhooks;
use App\Models\AmoCRMAccount;
use App\Models\Bill;
use App\Models\NDSType;
use App\Models\OPFType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;


class AmoCRMController extends Controller
{
    /**
     
     * [NOTES]
     *
     * > hash-key
     * В аккаунте используется значение hash - выступает в роли ключа API для обеспечения
     * безопасности работы виджета с аккаунтом Linerfin.
     * Этот ключ сохраняется в настройках виджета и в базе Linerfin.
     */


    use JsonResponses;

    protected $apiClient;

    public function __construct()
    {
        $this->apiClient = new AmoCRMApiClient(
            env('amocrm_client_id', ''),
            env('amocrm_client_secret', ''),
            env('amocrm_client_redirect', '')
        );
    }

    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/connect",
     *      operationId="connect",
     *      tags={"AMOcrmClient"},
     *      summary="Обмен authorization code на access/refresh tokens",
     *      description="Ваш ключ - это значение, которое используется для обеспечения безопасности работы 
     *      виджета с аккаунтом Linerfin. Это значение сохраняется в настройках виджета и в базе данных Linerfin.",
     *      @OA\Parameter(
     *          name="referer",
     *          in="query",
     *          required=true,
     *          description="адрес аккаунта пользователя",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="productlab"
     *      ),

     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          required=true,
     *          description="Код авторизации приложения",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="fece5944-fa42-4630-973f-d69e4c2cf7d4"
     *      ),
     *      
     *      @OA\Response(
     *          response=200,
     *          description="Успешное обмен authorization code на access/refresh tokens",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Ошибка валидации параметров",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Ошибка доступа",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Ошибка обмена authorization code на access/refresh tokens",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      )
     * )
     */

    public function OAuthRegistration(Request $request)
    {
        $apiClient = $this->apiClient;

        if ($referer = $request->get('referer'))
            $apiClient->setAccountBaseDomain($referer);

        if (!$code = $request->get('code'))
            die(403);


        try {
            $accessToken = $this->apiClient->getOAuthClient()->getAccessTokenByCode($code);

            if (!$accessToken->hasExpired()) {
                $amoCRM = $this->saveToken($accessToken, $referer);
                BindWebhooks::dispatch($amoCRM); // bind webhooks
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error($error);
            die();
        }

        echo 'success';
    }

    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/get-token",
     *      operationId="getPersonalAccessToken",
     *      tags={"AMOcrmClient"},
     *    
     *      summary="Получение персонального доступа к аккаунту",
     *      description="Ваш ключ - это значение, которое используется для обеспечения безопасности работы виджета с аккаунтом Linerfin. Это значение сохраняется в настройках виджета и в базе данных Linerfin.",
     *     
     *      @OA\Parameter(
     *          name="subdomain",
     *          in="query",
     *          required=true,
     *          description="Значение subdomain, которое используется для идентификации аккаунта в системе AmoCRM",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="productlab"
     *      ),

     *      @OA\Parameter(
     *          name="hash",
     *          in="query",
     *          required=true,
     *          description="Значение hash, которое используется для обеспечения безопасности работы виджета с аккаунтом Linerfin",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="fece5944-fa42-4630-973f-d69e4c2cf7d4"
     *      ),
     
     *      @OA\Response(
     *          response=200,
     *          description="Успешное получение персонального доступа к аккаунту",
     *           @OA\JSONContent(
     *              @OA\Property(property="token",type="string",example="40|Z82bi7FUCtczZnwK9WllxQ8yDbWNajtot52LYPBT"),
     *              @OA\Property(property="Success",type="boolean",example="true"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Ошибка валидации параметров",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Ошибка доступа",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Аккаунт не найден",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      )
     * ),
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/sanctum/csrf-cookie",
     *      operationId="GetSCRF",
     *      tags={"AMOcrmClient"},
     *    
     *      summary="Установка Cookies CSRF token",
     *      description="При переходе по данному маршруту в файлах Cookies устанавливается CSRF token",
     *     
     *  
     *      @OA\Response(
     *          response=200,
     *          description="Установка Cookies CSRF token",
     *         
     *      )
     * )
     */
    public function getPersonalAccessToken(Request $request)
    {
        $subdomain = $request->get('subdomain');
        $hash = $request->get('hash');

        if (empty($subdomain))
            return $this->error(['error' => 'subdomain param is required'], 'Аккаунт не найден');

        $account = AmoCRMAccount::whereSubdomain($subdomain)->first();
        if (!$account) return $this->success();

        if ($account->hash && $account->hash !== $hash)
            return $this->error(['errors' => ['hash' => 'hash token has expired']], 'Доступ ограничен');

        return $this->success(['token' => $account->personal_access_token]);
    }


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/bootstrap",
     *      operationId="bootstrap",
     *      tags={"AMOcrmClient"},
     *      summary="Получение информации о пользователе и его аккаунтах",
     *      description="Возвращает информацию о пользователе и его аккаунтах. Для каждого аккаунта возвращаются списки контрагентов и счетов.",
     *      
     *      security={{"bearerAuth":{}}},  
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="surname", type="string"),
     *                 @OA\Property(property="patronymic", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="accounts", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="user_id", type="integer"),
     *                          @OA\Property(property="opf_type_id", type="integer"),
     *                          @OA\Property(property="nds_type_id", type="integer"),
     *                          @OA\Property(property="taxation_system_id", type="integer"),
     *                          @OA\Property(property="type", type="string"),
     *                          @OA\Property(property="subdomain", type="string"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="inn", type="integer"),
     *                          @OA\Property(property="kpp", type="integer"),
     *                          @OA\Property(property="ogrn", type="integer"),
     *                          @OA\Property(property="address", type="string"),
     *                          @OA\Property(property="legal_address", type="string"),
     *                          @OA\Property(property="director_position", type="string"),
     *                          @OA\Property(property="director_name", type="string"),
     *                          @OA\Property(property="accountant_position", type="string"),
     *                          @OA\Property(property="accountant_name", type="string"),
     *                          @OA\Property(property="director_signature_id", type="integer"),
     *                          @OA\Property(property="accountant_signature_id", type="integer"),
     *                          @OA\Property(property="logo_attachment_id", type="integer"),
     *                          @OA\Property(property="stamp_attachment_id", type="integer"),
     *                          @OA\Property(property="is_demo", type="integer"),
     *                          @OA\Property(property="deleted_at", type="date"),
     *                          @OA\Property(property="created_at", type="date"),
     *                          @OA\Property(property="updated_at", type="date"),
     *                          @OA\Property(property="counterparties", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer"),
     *                                  @OA\Property(property="account_id", type="integer"),
     *                                  @OA\Property(property="name", type="string"),
     *                                  @OA\Property(property="type", type="string"),
     *                                  @OA\Property(property="category_id", type="integer"),
     *                                  @OA\Property(property="opf_type_id", type="string"),
     *                                  @OA\Property(property="inn", type="string"),
     *                                  @OA\Property(property="ogrn", type="string"),
     *                                  @OA\Property(property="kpp", type="string"),
     *                                  @OA\Property(property="address", type="string"),
     *                                  @OA\Property(property="legal_address", type="string"),
     *                                  @OA\Property(property="comment", type="string"),
     *                                  @OA\Property(property="deleted_at", type="date"),
     *                                  @OA\Property(property="created_at", type="date"),
     *                                  @OA\Property(property="updated_at", type="date"),
     *                                  @OA\Property(property="amo_company_id", type="integer")
     *                              )
     *                          )
     *                      )
     *                  )     
     *             ),
     *                 @OA\Property(property="accounts", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="user_id", type="integer"),
     *                          @OA\Property(property="opf_type_id", type="integer"),
     *                          @OA\Property(property="nds_type_id", type="integer"),
     *                          @OA\Property(property="taxation_system_id", type="integer"),
     *                          @OA\Property(property="type", type="string"),
     *                          @OA\Property(property="subdomain", type="string"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="inn", type="integer"),
     *                          @OA\Property(property="kpp", type="integer"),
     *                          @OA\Property(property="ogrn", type="integer"),
     *                          @OA\Property(property="address", type="string"),
     *                          @OA\Property(property="legal_address", type="string"),
     *                          @OA\Property(property="director_position", type="string"),
     *                          @OA\Property(property="director_name", type="string"),
     *                          @OA\Property(property="accountant_position", type="string"),
     *                          @OA\Property(property="accountant_name", type="string"),
     *                          @OA\Property(property="director_signature_id", type="integer"),
     *                          @OA\Property(property="accountant_signature_id", type="integer"),
     *                          @OA\Property(property="logo_attachment_id", type="integer"),
     *                          @OA\Property(property="stamp_attachment_id", type="integer"),
     *                          @OA\Property(property="is_demo", type="integer"),
     *                          @OA\Property(property="deleted_at", type="date"),
     *                          @OA\Property(property="created_at", type="date"),
     *                          @OA\Property(property="updated_at", type="date"),
     *                          @OA\Property(property="counterparties", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer"),
     *                                  @OA\Property(property="account_id", type="integer"),
     *                                  @OA\Property(property="name", type="string"),
     *                                  @OA\Property(property="type", type="string"),
     *                                  @OA\Property(property="category_id", type="integer"),
     *                                  @OA\Property(property="opf_type_id", type="string"),
     *                                  @OA\Property(property="inn", type="string"),
     *                                  @OA\Property(property="ogrn", type="string"),
     *                                  @OA\Property(property="kpp", type="string"),
     *                                  @OA\Property(property="address", type="string"),
     *                                  @OA\Property(property="legal_address", type="string"),
     *                                  @OA\Property(property="comment", type="string"),
     *                                  @OA\Property(property="deleted_at", type="date"),
     *                                  @OA\Property(property="created_at", type="date"),
     *                                  @OA\Property(property="updated_at", type="date"),
     *                                  @OA\Property(property="amo_company_id", type="integer")
     *                              )
     *                          )
     *                      )
     *                  ),   
     *                  @OA\Property(property="nds_types", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="percentage", type="string")
     *                      )
     *                  ),
     *                  @OA\Property(property="opf_types", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="short_name", type="string"),
     *                          @OA\Property(property="type", type="string"),
     *                          @OA\Property(property="code", type="integer"),
     *                          @OA\Property(property="for_individual", type="integer"),
     *                          @OA\Property(property="for_legal", type="integer")
     *                      )
     *                  ),
     * 
     *          @OA\Property(property="success", type="boolean")
     *         )
     *     ),
     *     security={
     *         {"bearer": {}}
     *      }
     * )
     */

    public function accountBootstrap(Request $request)
    {
        $user = $request->user();
        $accounts = $user->accounts;
        $accounts->load('counterparties', 'checkingAccounts');
        $nds_types = NDSType::all();
        $opf_types = OPFType::all();
        return $this->success(compact('user', 'accounts', 'nds_types', 'opf_types'));
    }


    /**
     * Сохранение настроек интеграции
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function saveSettings(AmoCRMSettingsRequest $request)
    {
        // inputs:
        // < account-hash
        // < subdomain
        // < client-id
        // < user email
        // > result status [success|error]


        /* STEP: Get inputs
        -------------------------------------------*/
        $validated = $request->validated();
        $subdomain = $validated['subdomain'];
        $hash = $validated['account_hash'];


        /* STEP: Check requires
        -------------------------------------------*/
        if (empty($subdomain))
            return $this->error('Ошибка сохранения настроек: неверный subdomain');


        /* STEP: Get account or create
        -------------------------------------------*/
        $account = AmoCRMAccount::firstOrNew(compact('subdomain'), compact('hash'));

        if ($account->hash && $account->hash !== $hash)
            return $this->error('Ошибка сохранения настроек: доступ заблокирован');


        /* STEP: Update account info
        -------------------------------------------*/
        $account->fill($validated);
        $account->save();


        return $this->success(compact('account'));
    }


    public function saveToken(AccessTokenInterface $token, string $baseDomain)
    {
        $subdomain = self::getSubdomainFromDomain($baseDomain);

        return AmoCRMAccount::updateOrCreate(['subdomain' => $subdomain], [
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires' => $token->getExpires(),
            'base_domain' => $baseDomain
        ]);
    }

    public function getToken(string $subdomain, string $hash = '')
    {
        $builder = AmoCRMAccount::where('subdomain', $subdomain);
        if ($hash) $builder->where('hash', $hash);

        $model = $builder->first();
        if (!$model || !$model->access_token || !$model->refresh_token || !$model->expires)
            return false;

        return new AccessToken([
            'access_token' => $model->access_token,
            'refresh_token' => $model->refresh_token,
            'expires' => $model->expires,
            'baseDomain' => $model->base_domain
        ]);
    }

    public static function getSubdomainFromDomain(string $baseDomain)
    {
        if (preg_match('/^([^.]+)/ui', $baseDomain, $matches)) {
            return $matches[0];
        }

        return '';
    }


    public static function getAccount()
    {

        /** @var Request $request */
        $request = app()->make('request');
        $user = $request->user();
        if (!$user)
            return false;


        /** @var PersonalAccessToken $token */
        $token = $user->currentAccessToken();

        if (!$token)
            return false;

        // Get token as plain text
        $tokenPlain = null;
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            if (strpos($headers['Authorization'], 'Bearer') !== false) {
                $tokenPlain = str_replace('Bearer ', '', $headers['Authorization']);
            }
        }
        if (!$tokenPlain)
            return false;

        // Find account by token
        return AmoCRMAccount::where('personal_access_token', $tokenPlain)->first();
    }
    /**
     * @OA\Get( 
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/bills",
     *      operationId="bills",
     *      tags={"AMOcrmClient"},
     *      summary="Получение информации о счетах",
     *      description="Возвращает данные об аккаунте и связанных с ним счетах",
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="bills", type="array",
     *                 @OA\Items(
     *                      @OA\Property(property="id", type="integer"),
     *                      @OA\Property(property="account_id", type="integer"),
     *                      @OA\Property(property="nds_type_id", type="integer"),
     *                      @OA\Property(property="template_id", type="integer"),
     *                      @OA\Property(property="amocrm_account_id", type="integer"),
     *                      @OA\Property(property="amocrm_lead_id", type="integer"),
     *                      @OA\Property(property="amocrm_customer_id", type="integer"),
     *                      @OA\Property(property="counterparty_id", type="integer"),
     *                      @OA\Property(property="checking_account_id", type="integer"),
     *                      @OA\Property(property="stamp_attachment_id", type="integer"),
     *                      @OA\Property(property="logo_attachment_id", type="integer"),
     *                      @OA\Property(property="num", type="integer"),
     *                      @OA\Property(property="sum", type="integer"),
     *                      @OA\Property(property="sum_without_vat", type="integer"),
     *                      @OA\Property(property="pay_before", type="date"),
     *                      @OA\Property(property="status", type="string"),
     *                      @OA\Property(property="issued_at", type="date"),
     *                      @OA\Property(property="rejected_at", type="date"),
     *                      @OA\Property(property="paid_at", type="date"),
     *                      @OA\Property(property="realized_at", type="date"),
     *                      @OA\Property(property="payer_phone", type="string"),
     *                      @OA\Property(property="payer_email", type="string"),
     *                      @OA\Property(property="enable_attachments", type="integer"),
     *                      @OA\Property(property="comment", type="string"),
     *                      @OA\Property(property="reject_comment", type="string"),
     *                      @OA\Property(property="link", type="string"),
     *                      @OA\Property(property="private_key", type="string"),
     *                      @OA\Property(property="access", type="string"),
     *                      @OA\Property(property="deleted_at", type="date"),
     *                      @OA\Property(property="created_at", type="date"),
     *                      @OA\Property(property="updated_at", type="date"),
     *                      @OA\Property(property="positions", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="id", type="integer"),
     *                              @OA\Property(property="account_id", type="integer"),
     *                              @OA\Property(property="bill_id", type="integer"),
     *                              @OA\Property(property="name", type="string"),
     *                              @OA\Property(property="vendor_code", type="string"),
     *                              @OA\Property(property="unit_price", type="integer"),
     *                              @OA\Property(property="count", type="integer"),
     *                              @OA\Property(property="units", type="string"),
     *                              @OA\Property(property="nds_type_id", type="integer"),
     *                              @OA\Property(property="nds_type", type="integer")
     *                          )
     *                      ),
     *                      @OA\Property(property="signature_list_with_attachments", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="id", type="integer"),
     *                              @OA\Property(property="account_id", type="integer"),
     *                              @OA\Property(property="bill_id", type="integer"),
     *                              @OA\Property(property="position", type="string"),
     *                              @OA\Property(property="full_name", type="string"),
     *                              @OA\Property(property="signature_attachment_id", type="integer"),
     *                              @OA\Property(property="signature_attachment", type="string"))
     *                      ),
     *                      @OA\Property(property="checking_account", type="object",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="account_id", type="integer"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="num", type="string"),
     *                          @OA\Property(property="balance", type="integer"),
     *                          @OA\Property(property="bank_name", type="string"),
     *                          @OA\Property(property="bank_bik", type="string"),
     *                          @OA\Property(property="bank_swift", type="string"),
     *                          @OA\Property(property="bank_inn", type="integer"),
     *                          @OA\Property(property="bank_kpp", type="integer"),
     *                          @OA\Property(property="bank_correspondent", type="integer"),
     *                          @OA\Property(property="comment", type="string"),
     *                          @OA\Property(property="deleted_at", type="date"),
     *                          @OA\Property(property="created_at", type="date"),
     *                          @OA\Property(property="updated_at", type="date"),
     *                          @OA\Property(property="provider", type="string"),
     *                          @OA\Property(property="o_auth_account_id", type="integer"),
     *                          @OA\Property(property="provider_account_id", type="integer"),
     *                          @OA\Property(property="import_is_active", type="boolean"),
     *                          @OA\Property(property="provider_account_updated_at", type="date"),
     *                          @OA\Property(property="provider_account_created_at", type="date"),
     *                      ),
     *                      @OA\Property(property="counterparty", type="object",
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="account_id", type="integer"),
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="type", type="string"),
     *                          @OA\Property(property="category_id", type="integer"),
     *                          @OA\Property(property="opf_type_id", type="string"),
     *                          @OA\Property(property="inn", type="string"),
     *                          @OA\Property(property="ogrn", type="string"),
     *                          @OA\Property(property="kpp", type="string"),
     *                          @OA\Property(property="address", type="string"),
     *                          @OA\Property(property="legal_address", type="string"),
     *                          @OA\Property(property="comment", type="string"),
     *                          @OA\Property(property="deleted_at", type="date"),
     *                          @OA\Property(property="created_at", type="date"),
     *                          @OA\Property(property="updated_at", type="date"),
     *                          @OA\Property(property="amo_company_id", type="integer"),
     *                      ),
     *                      @OA\Property(property="transactions", type="array",
     *                          @OA\Items()
     *                      ),
     *                      @OA\Property(property="stamp_attachment", type="string"),
     *                      @OA\Property(property="logo_attachment", type="string"),
     *                 )
     *             ),
     *          @OA\Property(property="account", type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="opf_type_id", type="integer"),
     *                  @OA\Property(property="nds_type_id", type="integer"),
     *                  @OA\Property(property="taxation_system_id", type="integer"),
     *                  @OA\Property(property="type", type="string"),
     *                  @OA\Property(property="subdomain", type="string"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="inn", type="integer"),
     *                  @OA\Property(property="kpp", type="integer"),
     *                  @OA\Property(property="ogrn", type="integer"),
     *                  @OA\Property(property="address", type="string"),
     *                  @OA\Property(property="legal_address", type="string"),
     *                  @OA\Property(property="director_position", type="string"),
     *                  @OA\Property(property="director_name", type="string"),
     *                  @OA\Property(property="accountant_position", type="string"),
     *                  @OA\Property(property="accountant_name", type="string"),
     *                  @OA\Property(property="director_signature_id", type="integer"),
     *                  @OA\Property(property="accountant_signature_id", type="integer"),
     *                  @OA\Property(property="logo_attachment_id", type="integer"),
     *                  @OA\Property(property="stamp_attachment_id", type="integer"),
     *                  @OA\Property(property="is_demo", type="integer"),
     *                  @OA\Property(property="deleted_at", type="date"),
     *                  @OA\Property(property="created_at", type="date"),
     *                  @OA\Property(property="updated_at", type="date")
     *          ),
     *          @OA\Property(property="success", type="boolean")
     *         )
     *     ),
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="query",
     *          description="Идентификатор сделки",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="50266407"
     *      ),
     *      @OA\Parameter(
     *          name="customer_id",
     *          in="query",
     *          description="Идентификатор покупателя",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *          example="1380807"
     *      ),
     *      security={{"bearerAuth":{}}},   
     *      @OA\Response(
     *          response=400,
     *          description="Ошибка валидации параметров",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Ошибка доступа",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Аккаунт не найден",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      ),
     *      security={
     *         {"bearer": {}}
     *      }
     * )
     */

    public function getBills(Request $request)
    {
        $account = self::getAccount();
        if (!$account)
            return $this->success(/*['headers' => getallheaders()]*/);

        // required once of lead_id and customer_id
        $lead_id = $request->get('lead_id');
        $customer_id = $request->get('customer_id');
        if (!$lead_id && !$customer_id)
            return $this->success(['bills' => []]);

        $builder = $account->bills()->where('status', '!=', 'template');

        if ($lead_id)
            $builder->where('amocrm_lead_id', $lead_id);
        elseif ($customer_id)
            $builder->where('amocrm_customer_id', $customer_id);

        $with = [
            'account', 'positions.nds_type',
            'signature_list_with_attachments',
            'checking_account', 'counterparty',
            'transactions', 'stamp_attachment',
            'logo_attachment'
        ];

        return $this->success([
            'bills' => $builder->with($with)->orderBy('created_at', 'DESC')->get(),
            'account' => $account
        ]);
    }
}
