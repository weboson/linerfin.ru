<?php

namespace App\Http\Controllers\AmoCRM\Sync;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UI\Transactions\Calculator;
use App\Http\Requests\AmoCRM\Webhooks\LeadStatusRequest;
use App\Http\Traits\ConsoleMsgTrait;
use App\Jobs\AmoCRM\HandleLeadStatusWebhook;
use App\Models\AmoCRMAccount;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadWebhookStatus extends Controller
{

    use ConsoleMsgTrait;


    const SuccessRealizedID = 142;
    const FailureRealizedID = 143;


    /**
     * Create Job on process webhook
     * @param LeadStatusRequest $request
     */
    public function __invoke(LeadStatusRequest $request)
    {
        HandleLeadStatusWebhook::dispatch($request->validated());
    }


    /**
     * Process webhook
     * @param array $validated
     */
    public function handle(array $validated){
        $amoAccount = AmoCRMAccount::whereSubdomain(Arr::get($validated, 'account.subdomain'))->first();
        if(!$amoAccount) {
            $this->consoleMsg('amoAccount not found');
            return;
        }

        if(!$amoAccount->bill_closing) {
            $this->consoleMsg('bill_closing disabled. Skip');
            return; // if bill closing disabled
        }

        $user = $amoAccount->referenceUser()->first();
        if(!$user){
            $this->consoleMsg('User not found');
            return;
        }
        Auth::login($user);

        /** @var Collection $account */
        $accounts = $user->accounts()->get();
        if(!$accounts) {
            $this->consoleMsg('Accounts not found. Skip');
            return;
        }


        $lead = Arr::get($validated, 'leads.status.0');

        // if status -> success realized lead
        if($lead['status_id'] == self::SuccessRealizedID){

            // get bills to processing
            $account_ids = $accounts->pluck('id');
            $bills = Bill::whereIn('account_id', $account_ids)
                ->whereNotIn('status', ['draft', 'realized', 'realized-paid'])
                ->whereNull('realized_at')
                ->whereNull('rejected_at')
                ->where('amocrm_lead_id', $lead['id'])
                ->get();

            $this->consoleMsg('Find bills: '.$bills->count());

            foreach($bills as $bill){

                $this->consoleMsg('Process bill ID '.$bill->id);

                // create transactions
                if(!$bill->paid_at){
                    $billSum = $bill->sum;
                    $factSum = $bill->transactions()->sum('amount');

                    // create transaction
                    $transactionSum = $billSum - $factSum;
                    if($transactionSum){
                        $transaction = new Transaction([
                            'amount' => $transactionSum,
                            'comment' => $bill->comment,
                            'type' => 'income',
                            'date' => Carbon::now()
                        ]);

                        // Relations
                        $transaction->account()->associate($bill->account_id);
                        $transaction->bill()->associate($bill);
                        $transaction->toCheckingAccount()->associate($bill->checking_account_id);
                        $transaction->counterparty()->associate($bill->counterparty_id);
                        $transaction->nds_type()->associate($bill->nds_type_id);

                        DB::transaction(function() use ($bill, $transaction){

                            // Save
                            $transaction->save();

                            // Made payment
                            $Calc = new Calculator($bill->account);
                            $Calc->Core->madePayment($transaction);

                            // Calculate
                            $Calc->calculateTotalBalance($transaction);
                            $Calc->calculateAccountBalances($transaction);

                            $bill->update(['paid_at' => Carbon::now()]);
                        });
                    }
                }

                // realize bill
                $bill->update(['realized_at' => Carbon::now()]);
            }
        }
    }
}
