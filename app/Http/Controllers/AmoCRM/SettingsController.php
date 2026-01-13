<?php


namespace App\Http\Controllers\AmoCRM;


use App\Http\Traits\JsonResponses;
use Illuminate\Http\Request;

class SettingsController extends \App\Http\Controllers\Controller
{

    protected $amoAccount;
    use JsonResponses;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $account = AmoCRMController::getAccount();
            if (!$account)
                return false;

            $this->amoAccount = $account;

            return $next($request);
        });
    }
    /**
     * @OA\Post(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *     path="/amocrm/linerfin-settings",
     *     operationId="linerfin-settings-store",
     *     summary="Установка параметров",
     *     tags={"AMOcrmClient"},
     * 
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="bill_closing", type="boolean"),
     *                          @OA\Property(property="task_creating", type="boolean")
     *                      )
     *                  }
     *          )
     *     ),
     * 
     *      security={{"bearerAuth":{}}}, 
     * 
     *     description="Устанавливаем количество закрытых счетов и сделок",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="settings", type="object",
     *                 @OA\Property(property="bill_closing", type="boolean"),
     *                 @OA\Property(property="task_creating", type="boolean")
     *             )
     *         )
     *     ),
     * 
     *     security={
     *         {"bearer": {}}
     *     }
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_closing' => 'boolean',
            'task_creating' => 'boolean'
        ]);

        $this->amoAccount->update($validated);

        return $this->success(['settings' => $validated]);
    }


    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *      path="/amocrm/linerfin-settings",
     *      operationId="linerfin-settings-show",
     *      tags={"AMOcrmClient"},
     *      summary="Получение параметров",
     *      description="Возвращает текущие настройки аккаунта AMOcrm.",
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="settings", type="object",
     *                 @OA\Property(property="bill_closing", type="boolean"),
     *                 @OA\Property(property="task_creating", type="boolean")
     *             ),
     *          @OA\Property(property="success", type="boolean")
     *         )
     *     ),
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

    public function show()
    {
        return $this->success([
            'settings' => $this->amoAccount->only('bill_closing', 'task_creating')
        ]);
    }
}
