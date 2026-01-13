<?php

namespace App\Jobs\AmoCRM;

use App\Http\Controllers\AmoCRM\CreateAmoWebhooks;
use App\Models\AmoCRMAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BindWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var AmoCRMAccount */
    public $amoAccount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AmoCRMAccount $account)
    {
        $this->amoAccount = $account;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new CreateAmoWebhooks($this->amoAccount))
            ->createWebhooks();
    }
}
