<?php

namespace App\Jobs\AmoCRM;

use App\Http\Controllers\AmoCRM\Sync\CompanyWebhookSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleAddCompanyWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validated;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $validated)
    {
        $this->validated = $validated;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $controller = new CompanyWebhookSync();
        $controller->handle($this->validated);
    }
}
