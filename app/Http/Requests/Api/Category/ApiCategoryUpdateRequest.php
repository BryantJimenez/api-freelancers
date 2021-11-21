<?php

namespace App\Http\Requests\Api\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiCategoryUpdateRequest extends FormRequest
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
        $categories=Category::where('id', '!=', $this->category->id)->get()->pluck('id');
        return [
            'name' => 'required|string|min:2|max:191|'.Rule::unique('categories')->ignore($this->category->slug, 'slug'),
            'category_id' => 'nullable|'.Rule::in($categories)
        ];
    }
}
