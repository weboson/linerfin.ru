<?php

namespace App\Http\Requests\AmoCRM\Webhooks;

use Illuminate\Foundation\Http\FormRequest;

class LeadStatusRequest extends FormRequest
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
            'leads' => ['required', 'array'],
            'leads.status' => ['required', 'array'],
            'leads.status.0' => ['required', 'array'],
            'leads.status.0.id' => ['required'],
            'leads.status.0.status_id' => ['required'],

            'account' => ['required', 'array'],
            'account.subdomain' => ['required']
        ];
    }
}
