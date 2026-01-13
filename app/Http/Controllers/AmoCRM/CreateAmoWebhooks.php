<?php


namespace App\Http\Controllers\AmoCRM;


use AmoCRM\Models\WebhookModel;
use App\Models\AmoCRMAccount;
use League\OAuth2\Client\Token\AccessToken;

class CreateAmoWebhooks extends \App\Http\Controllers\Controller
{

    protected $account;

    public function __construct(AmoCRMAccount $account){
        $this->account = $account;
    }

    public function createWebhooks(){
        $client = AmoCRMProvider::initClient($this->account);
        $webhookService = $client->webhooks();

        foreach($this->getWebhooks() as $webhook){
            $webhookService->subscribe(
                (new WebhookModel)
                    ->setDestination($webhook['url'])
                    ->setSettings($webhook['events'])
            );
        }
    }

    protected function getWebhooks(): array
    {
        return [
            [
                'url' => route('amocrm.webhooks.company.add'),
                'events' => ['add_company']
            ],
            [
                'url' => route('amocrm.webhooks.lead.status'),
                'events' => ['status_lead']
            ],
        ];
    }

}
