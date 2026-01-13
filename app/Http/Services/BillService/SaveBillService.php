<?php


namespace App\Http\Services\BillService;


use App\Models\Bill;

class SaveBillService
{

    protected $bill;
    protected $account;

    public function __construct(Bill $bill){
        $this->bill = $bill;
        $this->account = $bill->account;
    }

    public function runSaving(){
        $this->changeBillStatus()
            ->generateBillLink()
            ->generateBillNumber()
            ->setDefaultVAT();
    }


    protected function generateBillNumber(){
        if(!$this->account)
            return $this;

        if(!$this->bill->num && !in_array($this->bill->status, ['template', 'draft'])) {
            $billNumber = ($this->account->bills()->whereNotNull('num')->count() ?? 0) + 1;
            $this->bill->num = substr("000000" . $billNumber, -9);
        }

        return $this;
    }

    protected function generateBillLink(){
        // Create link
        if(!$this->bill->link)
            $this->bill->link = md5(uniqid(rand(), true));

        // Generate private key
        $this->bill->private_key = md5(uniqid(rand(), true));


        return $this;
    }

    protected function setDefaultVAT(){
        // def nds_type_id by account
        if(!$this->bill->nds_type_id){
            $account = $this->bill->account;
            if($account && $nds_type = $account->nds_type)
                $this->bill->nds_type()->associate($nds_type);
        }


        // update amount without VAT
        $sum = $this->bill->sum ?: 0;
        if($this->bill->nds_type && $this->bill->nds_type->percentage)
            $sum -= $sum * $this->bill->nds_type->percentage / 100;
        $this->bill->sum_without_vat = $sum;


        return $this;
    }

    protected function changeBillStatus(){
        if($this->bill->status === 'issued' && !$this->bill->issued_at)
            $this->bill->issued_at = date('Y-m-d h:i:s');

        // if is template
        if($this->bill->status === 'template')
            return $this;

        // if rejected
        elseif($this->bill->rejected_at)
            $this->bill->status = 'rejected';

        // for drafts
        elseif(!$this->bill->issued_at)
            $this->bill->status = 'draft';

        // if closed bills
        elseif($this->bill->realized_at && $this->bill->paid_at)
            $this->bill->status = 'realized-paid';

        elseif($this->bill->realized_at) // for realized
            $this->bill->status = 'realized';

        elseif($this->bill->paid_at)     // for paid
            $this->bill->status = 'paid';


        return $this;
    }
}
