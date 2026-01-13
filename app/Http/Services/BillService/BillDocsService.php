<?php


namespace App\Http\Services\BillService;


use App\Models\Bill;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;

class BillDocsService
{
    protected $bill;
    protected $account;

    public function __construct(Bill $bill){
        $this->bill = $bill;
        $this->account = $bill->account;
    }

    /**
     * Create Bill Document
     */
    public function run(){
        $bill = $this->bill;

        $existBillDoc = $bill->documents()->where('type', 'bill')->count();
        $existClosingActDoc = $bill->documents()->where('type', 'closing_act')->count();
        $user = Auth::user();
        if(!$user) return;


        if($bill->issued_at && !$existBillDoc){
            $issued_at = $bill->issued_at->format('d.m.Y H:i');
            $doc = new Document(['name' => "Счёт $bill->num от $issued_at", 'type' => 'bill']);
            $doc->account()->associate($bill->account);
            $doc->user()->associate($user);
            $doc->bill()->associate($bill);
            $doc->save();
        }
        else
            $bill->documents()->where('type', 'bill')->delete();

        if($bill->realized_at && !$existClosingActDoc){
            $realized_at = $bill->realized_at->format('d.m.Y H:i');
            $doc = new Document(['name' => "Акт закрытия $bill->num от $realized_at", 'type' => 'closing_act']);
            $doc->account()->associate($bill->account);
            $doc->user()->associate($user);
            $doc->bill()->associate($bill);
            $doc->save();
        }
        else
            $bill->documents()->where('type', 'closing_act')->delete();
    }
}
