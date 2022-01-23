<?php

namespace App\Http\Requests\Api\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
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
            'stripe_public' => 'nullable|string|min:1|max:191',
            'stripe_secret' => 'nullable|string|min:1|max:191',
            'paypal_public' => 'nullable|string|min:1|max:191',
            'paypal_secret' => 'nullable|string|min:1|max:191'
        ];
    }
}
