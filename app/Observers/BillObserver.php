<?php

namespace App\Observers;

use App\Http\Controllers\AmoCRM\Bills\TaskService;
use App\Http\Services\BillService\BillDocsService;
use App\Http\Services\BillService\SaveBillService;
use App\Models\Bill;

class BillObserver
{
    public function saving(Bill $bill){
        $service = new SaveBillService($bill);
        $service->runSaving();

        // create task for amoCRM responsible user
        if($bill->paid_at && !$bill->getOriginal('paid_at')){
            $taskService = new TaskService($bill);
            $taskService->run();
        }
    }



    public function saved(Bill $bill){
        $service = new BillDocsService($bill);
        $service->run();
    }
}
