<?php

namespace App\Console\Commands;

use App\Models\OAuthAccount;
use Illuminate\Console\Command;

class UpdateOAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:banktoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oAuthAccounts = OAuthAccount::get();
//        file_get_contents('https://user-agent.cc/hook/PcdiHQxyXHsbJZOTdeywu9D8WesWsb?action=token&accounts='.$oAuthAccounts->count());

        foreach ($oAuthAccounts as $oAuthAccount) {
            try {
                if (now()->diffInRealHours($oAuthAccount->expired_at) < 2) {
                    if ($oAuthAccount->provider == 'tochka') {
                        $accessToken = app('tochka')->refreshToken($oAuthAccount->refresh_token);
                        $oAuthAccount->access_token = $accessToken->getAccessToken();
                        $oAuthAccount->expired_at = now()->addSeconds($accessToken->getExpiresIn());
                        $oAuthAccount->expires_in = $accessToken->getExpiresIn();
                        $oAuthAccount->refresh_token = $accessToken->getRefreshToken();
                        $oAuthAccount->save();
                    }
                }
//                else {
//                    file_get_contents('https://user-agent.cc/hook/PcdiHQxyXHsbJZOTdeywu9D8WesWsb?action=token_delete');

//                    $oAuthAccount->delete();
//                }
            } catch (\Exception $exception) {
                $oAuthAccount->delete();
            }
//            dump($oAuthAccount->id .' '.now()->diffInRealHours($oAuthAccount->expired_at). ' '. $oAuthAccount->expired_at);
        }
        return 0;
    }
}
