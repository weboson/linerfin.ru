<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmoCRMSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subdomain' => 'required',
            'account_hash' => 'required',
            'client_id' => 'nullable',
            'client_name' => 'nullable',
            'first_name' => 'nullable',
            'email' => 'nullable',
            'user_id' => 'nullable',
            'timezone' => 'nullable',
            'status' => 'nullable',
            'active' => 'nullable',
            'widget_active' => 'nullable',
            'widget_code' => 'nullable',
            'tariff_name' => 'nullable',
            'users_count' => 'nullable',
            'paid_till_date' => 'nullable',
            'paid_from' => 'nullable'
        ];
    }
}
