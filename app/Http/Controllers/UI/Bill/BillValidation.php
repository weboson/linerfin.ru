<?php


namespace App\Http\Controllers\UI\Bill;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillValidation
{
    /*
     * 1. Правила валидации для публикации
     * 2. Правила валидации для черновика
     * 3. Для позиций
     * 4. Для подписного листа
     */


    // Правила валидации для черновика
    const validateDraftRules = [
        'id' =>                 'nullable|exists:App\Models\Bill,id',
        'template_id' =>        'nullable|exists:App\Models\BillTemplate,id',
        'nds_type_id' =>        'nullable|exists:App\Models\NDSType,id',
        'status' =>             'in:draft,issued,rejected,template,paid,realized,realized-paid',
        'stamp_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
        'logo_attachment_id' =>       'nullable|exists:App\Models\Attachment,id',
        'counterparty_id' =>       'nullable|exists:App\Models\Counterparty,id',
        'checking_account_id' =>   'nullable|exists:App\Models\CheckingAccount,id',
        'account_id' =>            'nullable|exists:App\Models\Account,id',
        'num' =>                'nullable|max:30',
        'pay_before' =>         'nullable|date',
        'payer_phone' =>        'nullable|max:25',
        'payer_email' =>        'nullable|email|max:100',
        'enable_attachments' => 'nullable|boolean',
        'amocrm_account_id' =>  'nullable|exists:App\Models\AmoCRMAccount,id',
        'amocrm_lead_id' =>     'nullable|numeric',
        'amocrm_customer_id' =>     'nullable|numeric',
        'comment' =>            'nullable|max:250'
    ];

    public static function draft($data = null, array $rules = [], &$errors = null){
        $valRules = self::validateDraftRules;
        if($rules) $valRules = array_merge($valRules, $rules);

        return self::runValidate($valRules, $data, $errors);
    }




    // Правила валидации для публикации (отправки)
    const validateRules = [
        'id' =>                 'nullable|exists:App\Models\Bill,id',
        'template_id' =>        'nullable|exists:App\Models\BillTemplate,id',
        'nds_type_id' =>        'nullable|exists:App\Models\NDSType,id',
        'status' =>             'in:draft,issued,rejected,template,paid,realized,realized-paid',
        'stamp_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
        'logo_attachment_id' =>       'nullable|exists:App\Models\Attachment,id',
        'counterparty_id' =>       'nullable|exists:App\Models\Counterparty,id',
        'checking_account_id' =>   'nullable|exists:App\Models\CheckingAccount,id',
        'account_id' =>            'nullable|exists:App\Models\Account,id',
        'num' =>                'nullable|max:30',
        'pay_before' =>         'nullable|date',
        'payer_phone' =>        'nullable|max:25',
        'payer_email' =>        'nullable|email|max:100',
        'draft' =>              'nullable|boolean',
        'enable_attachments' => 'nullable|boolean',
        'amocrm_account_id' =>  'nullable|exists:App\Models\AmoCRMAccount,id',
        'amocrm_lead_id' =>     'nullable|numeric',
        'comment' =>            'nullable|max:250',
    ];

    public static function toPublish($data = null, array $rules = [], &$errors = null){
        $valRules = self::validateRules;
        if($rules) $valRules = array_merge($valRules, $rules);

        return self::runValidate($valRules, $data, $errors);
    }


    // Валидация позиций счета
    const validatePosition = [
        'name' => 'required|max:255',
        'unit_price' => 'required|numeric|min:0',
        'count' => 'required|numeric|min:1',
        'units' => 'nullable|max:50',
        'nds_type' => 'nullable|exists:App\Models\NDSType,id'
    ];

    public static function position($data = null, array $rules = [], &$errors = null){
        $valRules = self::validatePosition;
        if($rules) $valRules = array_merge($valRules, $rules);

        return self::runValidate($valRules, $data, $errors);
    }




    // Валидация подписей
    const validateSignature = [
        'position' => 'nullable|max:150',
        'full_name' => 'nullable|max:150',
        'signature_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
    ];

    public static function signature($data = null, array $rules = [], &$errors = null){
        $valRules = self::validateSignature;
        if($rules) $valRules = array_merge($valRules, $rules);

        return self::runValidate($valRules, $data, $errors);
    }




    /*
     * ... RUN! .. TDDDDDDDRDDDD!!!
     */

    protected static function runValidate(array $rules, $data = null, &$errors = null){
        if(empty($data))
            $data = app()->make('request');

        if($data instanceof Request)
            return $data->validate($rules);

        elseif(is_array($data)){
            $validator = Validator::make($data, $rules);
            if($validator->fails()){
                $errors = $validator->errors()->toArray();
                return false;
            }

            return $validator;
        }

        return false;
    }

}
