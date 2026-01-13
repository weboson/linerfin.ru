<?php


namespace App\Http\Controllers\DemoAccount;


use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;

abstract class BuilderAbstract extends Controller
{

    /** @var User|null  */
    protected $user = null;

    /** @var Account|null */
    protected $account = null;

    public function __construct(User $user, Account $account)
    {
        $this->user = $user;
        $this->account = $account;
    }

    abstract public function build();
}
