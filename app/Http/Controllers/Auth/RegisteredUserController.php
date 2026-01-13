<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DemoAccount\MainController;
use App\Http\Responses\JsonResponse;
use App\Models\Account;
use App\Models\OPFType;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    
    {
        return view('auth2.register');
    }

    /*
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        // Base validation
        $validated = $request->validate([
            'name' => 'required|string|max:300',
            'phone' => ['nullable'],['string'],['max:150'],['unique:users'],['regex:/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $errors = new MessageBag();


        // Get surname, name and patronymic
        $name = trim(preg_replace('/[ ]+/i', ' ', $request->name));
        if(empty($name)){
            $request->flash();
            $errors->add('name', 'Поле "Фамилия, Имя и Отчество" некорретно');
            return view('auth2.register')->withErrors($errors);
        }

        list($surname, $name, $patronymic) = array_pad(explode(' ', $name), 3, '');

        // Validate Full name
        if(empty($surname) || empty($name)){

            $request->flash();
            $errors->add('name', 'Поле "Фамилия, Имя и Отчество" некорретно');
            return view('auth2.register')->withErrors($errors);
        }

        // validate phone
        $phone = session('mobile');
        if(preg_match('/[a-z]/i', $validated['phone'])){
            $request->flash();
            $errors->add('phone', 'Неверный номер телефона');
            return view('auth2.register')->withErrors($errors);
        }

        $phone = preg_replace("/[^+0-9]/", "", $phone);
        /*if(empty($phone)){
            $request->flash();
            if(!empty($validated['phone']))
                $errors->add('phone', 'Для продолжения подтвердите номер телефона');
            else
                $errors->add('phone', 'Заполните номер телефона');

            return view('auth2.register')->withErrors($errors);

        }*/


        // check phone code [disabled]
       /* if(empty($validated['phone_code']) || empty(session('mobile_code'))){
            $request->flash();
            $errors->add('phone', 'Для продолжения подтвердите номер телефона');
            return view('auth2.register')->withErrors($errors);
        }
        elseif(!Hash::check($validated['phone_code'], session()->pull('mobile_code'))){
            $request->flash();
            $errors->add('phone', 'Неверный код подтверждения');
            return view('auth2.register')->withErrors($errors);
        }*/

        /*if(User::where('phone', 'like', $phone)->count()){

            $request->flash();
            $errors->add('phone', 'Пользователь с таким номером телефона уже существует. Воспользуйтесь формой восстановления пароля');
            return view('auth2.register', [
                'suggestRecovery' => urlencode($phone)
            ])->withErrors($errors);
        }*/



        Auth::login($user = User::create([
            'surname' => $surname,
            'name' => $name,
            'patronymic' => $patronymic,
            'phone' => $phone,
            'email' => $validated['email'],
            'password' => Hash::make($request->password),
        ]));

        event(new Registered($user));

        return redirect()->route('generate-demo');
    }



    public function createUserPhoneToken(Request $request){

        // check phone
        $validator = Validator::make($request->toArray(), [
            'phone' => ['required'],['string'],['max:150'],['unique:users'],['regex:/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/']
        ]);

        if($validator->fails()){
            return new \Illuminate\Http\JsonResponse(['success' => false, 'msg' => 'Неверный номер телефона', 'errors' => $validator->errors()]);
        }

        $phone = $request->phone;
        if(empty($phone) || empty(preg_replace("/[^+0-9]/", "", $phone)) || preg_match('/[a-z]/i', $phone))
            return new \Illuminate\Http\JsonResponse(['success' => false, 'msg' => 'Неверный номер телефона']);

        // check on retry sending
        $lastSending = session('mobile_code_send_at');
        if(!empty($lastSending) && $lastSending > time() - 30)
            return new \Illuminate\Http\JsonResponse(['success' => false, 'msg' => 'Повторите позже']);

        $phone = preg_replace("/[^+0-9]/", "", $phone);

        // generate code
        $code = substr(str_shuffle('00112233445566778899'), 1, 5);
        session(['mobile_code' => Hash::make($code), 'mobile' => $phone, 'mobile_code_send_at' => time()]);

        // send code to client
        if(!$this->sendPhoneCode($phone, $code))
            return new JsonResponse(['success' => false, 'msg' => 'Не удалось отправить код. Проверьте введенный номер телефона']);

        return new JsonResponse(['success' => true, 'test' => [session('mobile'), session('mobile_code')]]);
    }


    public function sendPhoneCode($phone, $code){
        $apiId = config('app.SMS_RU_API', '');
        $client = new \Zelenin\SmsRu\Api(new \Zelenin\SmsRu\Auth\ApiIdAuth($apiId), new \Zelenin\SmsRu\Client\Client());

        $sms = new \Zelenin\SmsRu\Entity\Sms($phone, "Код для регистрации в LinerFin: $code");
        if($client->smsSend($sms))
            return true;

        return false;
    }
}
