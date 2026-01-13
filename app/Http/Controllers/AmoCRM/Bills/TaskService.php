<?php

namespace App\Http\Controllers\AmoCRM\Bills;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\TasksCollection;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\TaskModel;
use App\Http\Controllers\AmoCRM\AmoCRMProvider;
use App\Http\Controllers\Controller;
use App\Http\Traits\ConsoleMsgTrait;
use App\Models\Account;
use App\Models\AmoCRMAccount;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Http\Request;

class TaskService extends Controller
{

    use ConsoleMsgTrait;


    /**
     * @var Bill
     * @var Account
     * @var AmoCRMAccount
     * @var User
     */
    protected $bill;
    protected $account;
    protected $amoAccount;
    protected $user;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
        $this->account = $bill->account;
        $this->user = $this->account->user;
        $this->amoAccount = $this->user->amoCrmAccounts->first();
    }

    public function run(){


        if(!$this->availableRun())
            return;

        $client = AmoCRMProvider::initClient($this->amoAccount);

        $user_id = (int) $this->getResponsibleUserId($client);
        if(!$user_id){
            $this->consoleMsg('Responsible user not found');
            return;
        }

        $this->createTask($client, $user_id);
    }


    /**
     * Get responsible user by lead_id
     * @param AmoCRMApiClient $client
     */
    protected function getResponsibleUserId(AmoCRMApiClient $client)
    {
        $lead = $client->leads()->getOne($this->bill->amocrm_lead_id);
        if(!$lead){
            $this->consoleMsg("Lead ID ".$this->bill->amocrm_lead_id." not found");
            return false;
        }

        return $lead->getResponsibleUserId();
    }


    /**
     * Create task for responsible user
     * @param AmoCRMApiClient $client
     * @param int $responsible_user_id
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     */
    protected function createTask(AmoCRMApiClient $client, int $responsible_user_id){
        $collection = new TasksCollection();
        $task = new TaskModel();

        $task
            ->setTaskTypeId(TaskModel::TASK_TYPE_ID_FOLLOW_UP)
            ->setText('Счет оплачен. Связаться с клиентом')
            ->setCompleteTill(time() + 3600*24)
            ->setEntityType(EntityTypesInterface::LEADS)
            ->setEntityId($this->bill->amocrm_lead_id)
            ->setDuration(3600*24)
            ->setResponsibleUserId($responsible_user_id); // get user

        $collection->add($task);
        $client->tasks()->add($collection);
    }


    /**
     * Request valid
     * @return bool
     */
    protected function availableRun(): bool
    {
        if(!$this->amoAccount) {
            $this->consoleMsg('amoCRM account not exists. Exit');
            return false;
        }

        if(!$this->bill->amocrm_lead_id){
            $this->consoleMsg('bill not related with amoCRM lead. Exit');
            return false;
        }

        return true;
    }
}
