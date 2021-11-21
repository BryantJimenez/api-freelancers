<?php

namespace App\Http\Requests\Api\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiLanguageUpdateRequest extends FormRequest
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
            'code' => 'required|string|min:2|max:2',
            'name' => 'required|string|min:2|max:191|'.Rule::unique('languages')->ignore($this->language->id, 'id'),
            'native_name' => 'required|string|min:2|max:191'
        ];
    }
}
