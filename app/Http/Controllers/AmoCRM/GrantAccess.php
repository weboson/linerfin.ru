<?php

namespace App\Http\Controllers\AmoCRM;

use App\Http\Controllers\Controller;
use App\Models\AmoCRMAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GrantAccess extends Controller
{

    public function grantAccessView(Request $request)
    {
        $user = Auth::user();
        $subdomain = $request->get('account');
        $hash = $request->get('client-hash');
        if (!$user || !$subdomain)
            abort(404);

        return view('amocrm.grant-access', compact('user', 'subdomain', 'hash'));
    }

    /**
     * @OA\Get(
     *      @OA\Server(
     *          url="https://auth.linerfin.ru",
     *      ),
     *     path="/allow-access",
     *     summary="URL для открытия окна аутентификации в Linerfin и соотношения аккаунта AMOcrm",
     *      operationId="allow-access",
     *     tags={"AMOcrmClient"},
     *     @OA\Parameter(
     *          name="account",
     *          in="query",
     *          required=true,
     *          description="Значение account, которое используется для идентификации аккаунта в системе AmoCRM",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="productlab"
     *      ),

     *      @OA\Parameter(
     *          name="client-hash",
     *          in="query",
     *          required=true,
     *          description="Значение client-hash, которое используется для обеспечения безопасности работы виджета с аккаунтом Linerfin",
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          example="fece5944-fa42-4630-973f-d69e4c2cf7d4"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Возврат скрипта закрытия окна",
     *         
     *     )
     * )
     */

    public function grantAccessHandler(Request $request)
    {
        $user = $request->user();
        $subdomain = $request->get('account');
        $hash = $request->get('client-hash');

        $account = AmoCRMAccount::whereSubdomain($subdomain)->first();
        if (empty($account)) {
            $account = new AmoCRMAccount([
                'subdomain' => $subdomain,
                'hash' => $hash
            ]);
            $account->save();
        } else {
            $account->update(['hash' => $hash]);
        }
        /*elseif($account->hash && $account->hash !== $hash){
            echo 'Ошибка: Доступ заблокирован';
            die(403);
        }*/

        // Create new personal key
        $token = $user->createToken("Аккаунт amoCRM $subdomain.amocrm.ru");
        $account->referenceUser()->associate($user);
        $account->personal_access_token = $token->plainTextToken;
        $account->save();

        echo "<html lang=\"ru\"><body><script>window.close()</script></body></html>";
    }


    public function logout(Request $request)
    {
        $account = AmoCRMController::getAccount();
        $request->user()->currentAccessToken()->delete();

        if ($account) {
            $account->personal_access_token = null;
            $account->save();
        }

        return new JsonResource(['success' => true]);
    }
}
