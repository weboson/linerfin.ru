<?php

namespace App\Jobs\AmoCRM;

use App\Http\Controllers\AmoCRM\Sync\LeadWebhookStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleLeadStatusWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validated;

    /**
     * Create a new job instance.
     *
     * @param array $validated
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
        if(app()->runningInConsole())
            echo "Start handler\n";
        $controller = new LeadWebhookStatus();
        $controller->handle($this->validated);
    }
}
