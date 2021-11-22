<?php

namespace App\Http\Requests\Api\Publication;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiPublicationUpdateRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        $this->category_id=(!is_array($this->category_id)) ? explode(",", $this->category_id) : $this->category_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $categories=Category::get()->pluck('id');
        return [
            'name' => 'required|string|min:2|max:191',
            'description' => 'required|string|min:10|max:65000',
            'content' => 'required|string|min:10|max:65000',
            'category_id' => 'required|array',
            'category_id.*' => 'required|'.Rule::in($categories)
        ];
    }
}
