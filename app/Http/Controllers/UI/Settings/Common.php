<?php

namespace App\Http\Controllers\UI\Settings;

use App\Http\Abstraction\AccountAbstract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Common extends AccountAbstract
{
    // Controller handler of account's common settings


    /**
     * Save user name
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function saveUserName(Request $request){

        $name = trim(preg_replace('/[ ]+/i', ' ', $request->input('name')));

        if(empty($name) || mb_strlen($name) > 300)
            return $this->error('Фамилия, имя и отчество обязательно для ввода. Ограничение символов 300');


        // Get surname, name and patronymic
        list($surname, $name, $patronymic) = array_pad(explode(' ', $name), 3, '');

        if(empty($surname) || empty($name))
            return $this->error('Проверьте корректность ввода');

        $user = $this->user;
        $user->update(compact('surname', 'name', 'patronymic'));

        return $this->success();
    }


    /**
     * Save user phone
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function savePhone(Request $request){

        $phone = $request->input('phone');
        if(empty($phone))
            return $this->error();

        $this->user->update(compact('phone'));

        return $this->success();
    }


    /**
     * Save user e-mail
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function saveEmail(Request $request){
        $email = $request->input('email');
        if(empty($email))
            return $this->error();

        $this->user->update(compact('email'));

        return $this->success();
    }


    /**
     * Change password
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function changePassword(Request $request){
        $password = $request->input('password');
        if(empty($password) || mb_strlen($password) < 8)
            return $this->error('Пароль должен быть не менее 8 символов');

        if($request->input('r_password') !== $password)
            return $this->error('Пароли не совпадают');

        $this->user->update([
            'password' => Hash::make($password)
        ]);

        return $this->success();
    }
}
