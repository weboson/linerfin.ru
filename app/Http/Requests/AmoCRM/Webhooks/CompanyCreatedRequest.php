<?php

namespace App\Http\Requests\AmoCRM\Webhooks;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCreatedRequest extends FormRequest
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
            'contacts' => ['required', 'array'],
            'contacts.add' => ['required', 'array'],
            'contacts.add.*' => ['required', 'array'],
            'contacts.add.*.id' => ['required', 'numeric'],

            'account' => ['required', 'array'],
            'account.subdomain' => ['required']
        ];
    }
}
