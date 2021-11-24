<?php

namespace App\Http\Requests\Api\Proposal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiProposalUpdateRequest extends FormRequest
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
        $before_start=(!is_null($this->end)) ? '|before_or_equal:'.date('Y-m-d', strtotime($this->end)) : '';
        return [
            'start' => 'required|date_format:Y-m-d|after_or_equal:'.date('Y-m-d').$before_start,
            'end' => 'nullable|date_format:Y-m-d|after_or_equal:'.date('Y-m-d'),
            'amount' => 'required|string|min:1',
            'content' => 'required|string|min:10|max:65000'
        ];
    }
}
