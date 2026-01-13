<?php


namespace App\Http\Traits;


use App\Http\Responses\JsonResponse;

trait JsonResponses
{
    public function success($data = [], $msg = '', $status = 200, $options = []){

        if(!is_array($data))
            $data = [];

        if(is_string($data))
            $data = ['msg' => $data];

        if(!empty($msg)) $data['msg'] = $msg;
        $data['success'] = true;

        return new JsonResponse($data, $status);
    }

    public function error($data = [], $msg = '', $status = 200, $options = []){

        if(!is_array($data))
            $data = [];

        if(is_string($data))
            $data = ['msg' => $data];

        if(!empty($msg)) $data['msg'] = $msg;

        $data['success'] = false;
        if(empty($data['errors']))
            $data['errors'] = [];

        if(env('APP_DEBUG'))
            $data['backtrace'] = debug_backtrace();

        return new JsonResponse($data, $status);
    }

}
