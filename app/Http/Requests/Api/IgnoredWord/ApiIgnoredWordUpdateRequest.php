<?php

namespace App\Http\Requests\Api\IgnoredWord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiIgnoredWordUpdateRequest extends FormRequest
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
            'words' => 'required|string|min:2|max:191|'.Rule::unique('ignored_words')->ignore($this->word->id, 'id'),
        ];
    }
}
