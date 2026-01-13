<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsRuProvider extends Controller
{

    protected $client;

    public function __construct($api_key = null){
        $apiId = $api_key ?: config('app.SMS_RU_API', '');
        $this->client = new \Zelenin\SmsRu\Api(new \Zelenin\SmsRu\Auth\ApiIdAuth($apiId), new \Zelenin\SmsRu\Client\Client());
    }

    public function smsSend($to, $text = ''){
        $sms = new \Zelenin\SmsRu\Entity\Sms($to, $text);
        if($this->client->smsSend($sms))
            return true;

        return false;
    }


}
