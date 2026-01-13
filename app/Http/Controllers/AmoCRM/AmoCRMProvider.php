<?php


namespace App\Http\Controllers\AmoCRM;


use AmoCRM\Client\AmoCRMApiClient;
use App\Models\AmoCRMAccount;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class AmoCRMProvider
{
    public static function initClient(AmoCRMAccount $account): AmoCRMApiClient
    {

        $clientId = env('amocrm_client_id');
        $clientSecret = env('amocrm_client_secret');
        $redirectUri = env('amocrm_client_redirect');

        $apiClient = new \AmoCRM\Client\AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
        $apiClient->setAccessToken(self::getAccessToken($account))
            ->setAccountBaseDomain($account->base_domain)
            ->onAccessTokenRefresh(function (AccessTokenInterface $accessToken, string $baseDomain) use ($account) {
                $account->update([
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires()
                ]);
            });

        return $apiClient;
    }


    public static function getAccessToken(AmoCRMAccount $account): AccessToken
    {
        return new AccessToken([
            'access_token' => $account->access_token,
            'refresh_token' => $account->refresh_token,
            'expires' => $account->expires,
            'baseDomain' => $account->base_domain,
        ]);
    }
}
