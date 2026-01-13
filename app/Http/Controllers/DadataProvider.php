<?php

namespace App\Http\Controllers;

use Dadata\DadataClient;

class DadataProvider extends Controller
{
    const API_key = '46ba776444f714ce8699f20a1e121d6094428878';
    const Secret = '3d55f99bc9c103ac7c364d6941b86e73c7305501';


    public function __construct($api_key = '', $secret_key = ''){
        $this->client = new DadataClient(
            $api_key ?: self::API_key,
            $secret_key ?: self::Secret
        );
    }


    protected $client;
    public function getClient(){
        return $this->client;
    }

    public static function initClient($api_key = '', $secret_key = ''){
        return new DadataClient(
            $api_key ?: self::API_key,
            $secret_key ?: self::Secret
        );
    }
}
