<?php

namespace App\Http\Requests\Api\Profile;

use App\Models\Country;
use App\Models\Language;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiProfileFreelancerUpdateRequest extends FormRequest
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
        $this->language_id=(!is_array($this->language_id)) ? explode(",", $this->language_id) : $this->language_id;
        $this->category_id=(!is_array($this->category_id)) ? explode(",", $this->category_id) : $this->category_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $countries=Country::get()->pluck('id');
        $languages=Language::get()->pluck('id');
        $categories=Category::get()->pluck('id');
        return [
            'country_id' => 'required|'.Rule::in($countries),
            'description' => 'required|string|min:10|max:5000',
            'language_id' => 'required|array',
            'language_id.*' => 'required|'.Rule::in($languages),
            'category_id' => 'required|array',
            'category_id.*' => 'required|'.Rule::in($categories)
        ];
    }
}
