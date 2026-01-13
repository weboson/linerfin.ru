<?php

namespace App\Http\Controllers\AmoCRM\Bills;

use App\Http\Controllers\AmoCRM\AmoCRMController;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponses;
use App\Models\AmoCRMAccount;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyBills extends Controller
{
    use JsonResponses;


    public function index(Request $request){
        $validated = $request->validate([
            'company_id' => 'required|numeric'
        ]);

        $amoCompanyId = $validated['company_id'];

        /** @var AmoCRMAccount $account */
        $account = AmoCRMController::getAccount();
        if(!$account)
            return $this->error([], 'Ошибка доступа', 403);

        /** @var User $user */
        $user = $account->referenceUser()->first();
        if(!$user) return $this->error();

        $accounts_ids = $user->accounts()->pluck('id');

        /** @var Counterparty $counterparty */
        $counterparty = Counterparty::whereIn('account_id', $accounts_ids)
            ->where('amo_company_id', $amoCompanyId)->first();

        // nothing found
        if(!$counterparty)
            return $this->success();

        $bills = $counterparty->bills()->with([
            'account', 'positions.nds_type',
            'signature_list_with_attachments',
            'checking_account', 'counterparty',
            'transactions', 'stamp_attachment',
            'logo_attachment'
        ])->orderByDesc('created_at')->get();


        return $this->success(compact('bills'));
    }
}
